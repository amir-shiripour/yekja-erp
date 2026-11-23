@extends('layouts.user')

@php
    $title = 'ایجاد نقش جدید';
    $widgetGroups = collect($widgets ?? [])->groupBy('group');
    $oldWidgets = old('widgets', []);
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/80 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6 pb-16 md:pb-8">

        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-gradient-to-r from-white via-indigo-50/30 to-white dark:from-gray-800 dark:via-indigo-950/20 dark:to-gray-800 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                    <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        تعریف نقش سازمانی جدید
                    </h1>
                </div>
            </div>

            <a href="{{ isset($indexRoute) ? route($indexRoute) : route('admin.roles.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست نقش‌ها
            </a>
        </div>

        @if($errors->any())
            <x-ui.alert variant="error" title="خطا در اعتبارسنجی اطلاعات نقش" :dismissible="true">
                <ul class="list-disc list-inside space-y-1 mt-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ isset($storeRoute) ? route($storeRoute) : route('admin.roles.store') }}"
              class="space-y-6 pb-20" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            <div class="{{ $cardClass }}">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        مشخصات اصلی و نام‌گذاری نقش
                    </h2>
                    <x-ui.badge variant="indigo" size="sm">مرحله ۱</x-ui.badge>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="{{ $labelClass }}">نام فارسی نقش <span class="text-rose-500">*</span></label>
                        <input type="text" name="display_name" value="{{ old('display_name') }}"
                               class="{{ $inputClass }}" required placeholder="مثلاً: مدیر فروش و بازاریابی">
                        @error('display_name')
                        <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">شناسه لاتین (Slug)</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="{{ $inputClass }} dir-ltr text-left" placeholder="sales-manager">
                        <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">اختیاری؛ در صورت خالی بودن به طور
                            خودکار از روی نام فارسی تولید خواهد شد.</p>
                        @error('name')
                        <p class="text-rose-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="{{ $cardClass }}">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            ویجت‌های پیشخوان داشبورد
                        </h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 mr-4">ویجت‌هایی که کاربران دارای
                            این نقش در صفحه اصلی پنل کاربری مشاهده می‌کنند.</p>
                    </div>
                    <x-ui.badge variant="amber" size="sm">مرحله ۲</x-ui.badge>
                </div>

                <div class="p-6 space-y-6">
                    @forelse($widgetGroups as $groupLabel => $groupWidgets)
                        <div
                            class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-5 border border-gray-200/60 dark:border-gray-700/50">
                            <div class="mb-4 flex items-center gap-2">
                                <div class="w-1.5 h-4 bg-amber-500 rounded-full"></div>
                                <h3 class="text-xs font-black text-gray-800 dark:text-gray-200">{{ $groupLabel }}</h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($groupWidgets as $widget)
                                    @php
                                        $checked = is_array($oldWidgets) && array_key_exists($widget['key'], $oldWidgets);
                                    @endphp
                                    <label
                                        class="flex items-start gap-3 p-3.5 border rounded-2xl bg-white dark:bg-gray-800 border-gray-200/80 dark:border-gray-700/80 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors group shadow-xs">
                                        <input type="checkbox" name="widgets[{{ $widget['key'] }}]"
                                               class="mt-1 w-4 h-4 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" @checked($checked)>
                                        <div class="flex-1 space-y-1">
                                            <div
                                                class="font-bold text-xs text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $widget['label'] ?? $widget['key'] }}
                                            </div>
                                            @if(!empty($widget['description'] ?? null))
                                                <div
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                                    {{ $widget['description'] }}
                                                </div>
                                            @endif
                                            @if(!empty($widget['permission']))
                                                <div
                                                    class="text-[9px] bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 px-2 py-0.5 rounded-md inline-block dir-ltr mt-1">
                                                    مجوز: {{ $widget['permission'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="هیچ ویجتی تعریف نشده است"
                            description="در حال حاضر ماژول‌های سیستم ویجتی برای پیشخوان ارائه نکرده‌اند."
                        />
                    @endforelse
                </div>
            </div>

            <div class="{{ $cardClass }}"
                 x-data="permissionForm({
                    selected: @js(old('permissions', [])),
                    groups: @js($permissionGroups)
                 })">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white">
                                ماتریس مجوزها و سطوح دسترسی
                            </h2>
                            <x-ui.badge variant="emerald" size="sm">مرحله ۳</x-ui.badge>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 mr-4">دسترسی به بخش‌ها، عملیات‌ها و
                            ماژول‌های مختلف را برای این نقش مشخص کنید.</p>
                    </div>

                    <div class="relative w-full sm:w-72">
                        <input type="text" x-model="query" placeholder="جستجوی عنوان یا کلید مجوز..."
                               class="w-full h-10 rounded-2xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 pr-10 pl-4 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:text-white placeholder-gray-400 shadow-xs">
                        <div
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div
                        class="p-4 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                        <div
                            class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                میزان پوشش دسترسی‌های این نقش:
                            </span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold"
                                  x-text="selected.length + ' از ' + totalPermissionsCount + ' مجوز (' + coveragePercent + '%)'"></span>
                        </div>
                        <x-ui.progress bind="coveragePercent" color="indigo" size="md" :show-percent="false"/>
                    </div>
                    <template x-for="(group, key) in groups" :key="key">
                        <div
                            class="border border-gray-200/80 dark:border-gray-700/80 rounded-2xl overflow-hidden bg-white dark:bg-gray-800 shadow-xs"
                            x-show="visibleCount(key) > 0">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-gray-50/70 dark:bg-gray-900/50 border-b border-gray-200/80 dark:border-gray-700/80">
                                <button type="button"
                                        class="flex items-center gap-2 text-gray-900 dark:text-white font-bold text-xs outline-none group"
                                        x-on:click="toggle(key)">
                                    <svg
                                        class="w-4 h-4 text-gray-400 group-hover:text-indigo-600 transition-transform duration-200"
                                        :class="open[key] ? 'rotate-90 text-indigo-600' : 'rotate-180'" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    <span x-text="group.title"></span>
                                    <span
                                        class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 text-[10px] font-bold border border-indigo-100 dark:border-indigo-900/50">
                                        <span x-text="groupSelectedCount(key)"></span> / <span
                                            x-text="group.items.length"></span>
                                    </span>
                                </button>

                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[11px] font-bold dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 transition-colors"
                                            x-on:click="selectAll(key)">
                                        انتخاب همه
                                    </button>
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-[11px] font-bold dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                                            x-on:click="unselectAll(key)">
                                        حذف همه
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-show="open[key]"
                                 x-collapse>
                                <template x-for="perm in filtered(key)" :key="perm.name">
                                    <label
                                        class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200/70 bg-white hover:border-indigo-400 dark:border-gray-700/70 dark:bg-gray-800 dark:hover:border-indigo-500 cursor-pointer group transition-all shadow-xs">
                                        <input type="checkbox" name="permissions[]" :value="perm.name"
                                               :checked="selected.includes(perm.name)"
                                               @change="toggleOne(perm.name, $event.target.checked)"
                                               class="mt-1 w-4 h-4 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                        <div class="flex flex-col flex-1 min-w-0">
                                            <span
                                                class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate"
                                                x-text="perm.label"></span>
                                            <span
                                                class="text-[10px] text-gray-400 dark:text-gray-500 dir-ltr text-left mt-0.5 truncate"
                                                x-text="perm.name"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div
                        class="text-center py-8 bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700"
                        x-show="Object.keys(groups).reduce((acc, key) => acc + visibleCount(key), 0) === 0"
                        style="display: none;">
                        <p class="text-gray-500 dark:text-gray-400 text-xs">هیچ مجوزی مطابق با عبارت جستجو شده یافت
                            نشد.</p>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between relative md:sticky md:bottom-6 z-30 mb-6 md:mb-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md p-4 rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xl">
                <a href="{{ isset($indexRoute) ? route($indexRoute) : route('admin.roles.index') }}"
                   class="px-5 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>

                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting"
                          class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ثبت و ایجاد نقش جدید</span>
                    <span x-show="isSubmitting">در حال پردازش...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </div>

        </form>

        <div class="h-32 md:hidden" style="height: 8rem;" aria-hidden="true"></div>

        <x-ui.toast position="bottom-left"/>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('permissionForm', ({selected = [], groups = {}}) => ({
                query: '',
                selected,
                groups,
                open: Object.fromEntries(Object.keys(groups).map(k => [k, true])),

                get totalPermissionsCount() {
                    return Object.values(this.groups).reduce((acc, g) => acc + (g.items ? g.items.length : 0), 0) || 1;
                },

                get coveragePercent() {
                    return Math.min(100, Math.round((this.selected.length / this.totalPermissionsCount) * 100));
                },

                groupSelectedCount(key) {
                    const items = this.groups[key]?.items || [];
                    return items.filter(i => this.selected.includes(i.name)).length;
                },

                filtered(key) {
                    const q = this.query.trim().toLowerCase();
                    const items = this.groups[key].items;
                    if (!q) return items;
                    return items.filter(i => (i.label + ' ' + i.name).toLowerCase().includes(q));
                },

                visibleCount(key) {
                    return this.filtered(key).length;
                },

                toggle(key) {
                    this.open[key] = !this.open[key];
                },

                toggleOne(name, checked) {
                    if (checked && !this.selected.includes(name)) this.selected.push(name);
                    if (!checked) this.selected = this.selected.filter(n => n !== name);
                },

                selectAll(key) {
                    const names = this.filtered(key).map(i => i.name);
                    this.selected = Array.from(new Set([...this.selected, ...names]));
                },

                unselectAll(key) {
                    const names = this.filtered(key).map(i => i.name);
                    this.selected = this.selected.filter(n => !names.includes(n));
                }
            }));
        });
    </script>
@endsection
