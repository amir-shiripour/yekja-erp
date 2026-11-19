@php
    use App\Services\Modules\ModuleMenuService;$paletteItems = [];

    if (auth()->check() && class_exists(ModuleMenuService::class)) {
        try {
            $menuData = app(ModuleMenuService::class)->getAllForUser(auth()->user());

            foreach (($menuData['items'] ?? []) as $item) {
                $routeName = $item['route'] ?? null;
                if ($routeName && Route::has($routeName)) {
                    $paletteItems[] = [
                        'title' => $item['title'] ?? $routeName,
                        'group' => $item['group'] ?? 'عمومی',
                        'url' => route($routeName),
                    ];
                }
            }

            foreach (($menuData['groups'] ?? []) as $group) {
                $groupTitle = $group['title'] ?? ($group['module'] ?? 'ماژول');
                foreach (($group['items'] ?? []) as $sub) {
                    $routeName = $sub['route'] ?? null;
                    if ($routeName && Route::has($routeName)) {
                        $paletteItems[] = [
                            'title' => $sub['title'] ?? $routeName,
                            'group' => $groupTitle,
                            'url' => route($routeName),
                        ];
                    }
                }
            }

            foreach (($menuData['settings'] ?? []) as $sItem) {
                $routeName = $sItem['route'] ?? null;
                if ($routeName && Route::has($routeName)) {
                    $paletteItems[] = [
                        'title' => $sItem['title'] ?? $routeName,
                        'group' => 'تنظیمات',
                        'url' => route($routeName),
                    ];
                }
            }
        } catch (\Throwable) {}
    }

    // Core shortcuts
    if (Route::has('user.dashboard')) {
        array_unshift($paletteItems, [
            'title' => 'داشبورد و پیشخوان اصلی',
            'group' => 'میانبرهای اصلی',
            'url' => route('user.dashboard'),
        ]);
    }
    if (Route::has('user.content.themes') && auth()->user()?->can('content.themes.manage')) {
        $paletteItems[] = [
            'title' => 'استودیو پوسته‌ها و ظاهر CRM',
            'group' => 'تنظیمات و ظاهر',
            'url' => route('user.content.themes'),
        ];
    }

    // Deduplicate by URL
    $uniqueItems = [];
    $seenUrls = [];
    foreach ($paletteItems as $pi) {
        if (!isset($seenUrls[$pi['url']])) {
            $seenUrls[$pi['url']] = true;
            $uniqueItems[] = $pi;
        }
    }
@endphp

<div
    x-data="{
        open: false,
        query: '',
        activeIndex: 0,
        items: @js($uniqueItems),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.items.slice(0, 12);
            return this.items.filter(i =>
                (i.title && i.title.toLowerCase().includes(q)) ||
                (i.group && i.group.toLowerCase().includes(q))
            ).slice(0, 15);
        },
        openPalette() {
            this.open = true;
            this.query = '';
            this.activeIndex = 0;
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },
        moveDown() {
            if (this.filtered.length === 0) return;
            this.activeIndex = (this.activeIndex + 1) % this.filtered.length;
        },
        moveUp() {
            if (this.filtered.length === 0) return;
            this.activeIndex = (this.activeIndex - 1 + this.filtered.length) % this.filtered.length;
        },
        goActive() {
            const target = this.filtered[this.activeIndex];
            if (target && target.url) {
                window.location.href = target.url;
            }
        }
    }"
    @keydown.window="
        if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') {
            $event.preventDefault();
            open ? (open = false) : openPalette();
        }
    "
    @open-command-palette.window="openPalette()"
>
    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[9999] flex items-start justify-center pt-16 sm:pt-24 px-4 bg-gray-950/60 backdrop-blur-sm"
            @click.self="open = false"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="w-full max-w-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700/80 rounded-3xl shadow-2xl overflow-hidden"
            >
                {{-- Search Input --}}
                <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-100 dark:border-gray-800">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        x-ref="searchInput"
                        type="text"
                        x-model="query"
                        @input="activeIndex = 0"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="goActive()"
                        placeholder="جستجوی سریع صفحات، ماژول‌ها و تنظیمات..."
                        class="w-full bg-transparent text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none"
                    >
                    <kbd
                        class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">ESC</kbd>
                </div>

                {{-- Results List --}}
                <div class="max-h-80 overflow-y-auto p-2 space-y-1">
                    <template x-if="filtered.length === 0">
                        <div class="py-10 text-center text-xs text-gray-400">
                            موردی با عبارت مورد نظر یافت نشد.
                        </div>
                    </template>

                    <template x-for="(item, idx) in filtered" :key="item.url">
                        <a
                            :href="item.url"
                            @mouseenter="activeIndex = idx"
                            :class="activeIndex === idx
                                ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300'
                                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/60'"
                            class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-2xl text-xs transition-colors"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="activeIndex === idx ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"></span>
                                <span class="font-bold truncate" x-text="item.title"></span>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-medium text-gray-500 dark:text-gray-400 shrink-0"
                                x-text="item.group"></span>
                        </a>
                    </template>
                </div>

                {{-- Footer Hints --}}
                <div
                    class="px-4 py-2.5 bg-gray-50 dark:bg-gray-950/60 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-[11px] text-gray-400">
                    <span>انتخاب با کلیدهای ↑ ↓ و Enter</span>
                    <span class="font-mono">Ctrl + K</span>
                </div>
            </div>
        </div>
    </template>
</div>
