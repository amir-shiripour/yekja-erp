@if($addableModules->count() > 0)
    <div role="tabpanel" id="tabpanel-add_items" aria-labelledby="tab-add_items" x-show="activeTab === 'add_items'"
         x-data="addItemsManager()"
         class="space-y-6"
         x-cloak>
        <x-resellerhub::card
            class="p-6 sm:p-7 border border-gray-100 dark:border-gray-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5"
        >
            <div class="flex items-center gap-4">
                <div
                    class="w-13 h-13 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-center shadow-xs shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">مدیریت
                            ماژول‌ها و قابلیت‌های سفارش</h3>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/50">
                                {{ reseller_fa_num($addableModules->count()) }} ماژول قابل مدیریت
                            </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        ماژول‌ها و قابلیت‌های جدید را به این سفارش الحاق کنید یا موارد فعال جاری را حذف/کاهش
                        دهید.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-start md:self-auto shrink-0">
                <div
                    class="flex items-center gap-2 text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 px-4 py-2.5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 shadow-xs">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>دوره صورتحساب سفارش:</span>
                    <span
                        class="font-black text-indigo-600 dark:text-indigo-400">{{ $order->billing_cycle_label ?: reseller_duration_label($order->billing_cycle) }}</span>
                </div>
            </div>
        </x-resellerhub::card>

        {{-- Floating Bulk Removal Action Dock (Bottom-Center of Screen) --}}
        <div x-show="selectedForRemoval.length > 0"
             x-cloak
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 w-[calc(100%-2rem)] max-w-2xl"
             style="display: none;">
            <div
                class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-gray-200/90 dark:border-gray-800 shadow-2xl shadow-rose-950/15 dark:shadow-black/70 ring-1 ring-rose-500/20 dark:ring-rose-500/30 p-3 sm:p-4 flex items-center justify-between gap-3 sm:gap-4 text-right">
                {{-- Right Section: Icon + Information --}}
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/50 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                class="font-black text-xs sm:text-sm text-gray-900 dark:text-white">عملیات حذف گروهی</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 tabular-nums shrink-0"
                                x-text="toFa(selectedForRemoval.length) + ' مورد انتخاب شده'"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block mt-0.5 truncate">
                            موارد فوق پس از تایید از سفارش کسر شده و دسترسی آن‌ها لغو خواهد شد.
                        </p>
                    </div>
                </div>

                {{-- Left Section: Action Buttons --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button"
                            @click="clearRemovalSelection()"
                            class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold text-xs transition cursor-pointer">
                        لغو
                    </button>
                    <button type="button"
                            @click="promptBulkRemove()"
                            class="flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 active:scale-95 text-white font-black text-xs shadow-lg shadow-rose-600/30 transition cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>تایید و حذف گروهی</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Studio Split Layout: Modules Catalog (Right) + Dedicated Financial Summary (Left) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- RIGHT: Modules Catalog Cards --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">فهرست ماژول‌های سامانه</h4>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 font-bold"
                          x-show="selectedItems.length > 0"
                          x-text="toFa(selectedItems.length) + ' مورد برای افزودن انتخاب شده'"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($addableModules as $mod)
                        @php
                            $alreadyInOrder = !empty($mod['already_in_order']);
                        @endphp
                        <div
                            @click="!{{ json_encode($alreadyInOrder) }} && toggleModule({{ json_encode($mod) }})"
                            class="group relative rounded-3xl border-2 transition-all duration-200 flex flex-col justify-between overflow-hidden select-none"
                            :class="[
                                    {{ json_encode($alreadyInOrder) }}
                                        ? (isSelectedForRemoval('{{ $mod['slug'] }}')
                                            ? 'border-rose-400 bg-rose-50/40 dark:bg-rose-950/20 shadow-xs'
                                            : 'border-gray-200/90 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-800/60 shadow-xs')
                                        : isSelected('{{ $mod['slug'] }}')
                                            ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50/25 dark:bg-indigo-950/40 shadow-lg shadow-indigo-500/5 ring-2 ring-indigo-500/20 cursor-pointer'
                                            : 'border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800 hover:border-indigo-300 dark:hover:border-indigo-500/60 hover:shadow-md cursor-pointer'
                                ]">

                            {{-- Card Body --}}
                            <div class="p-5 space-y-3.5">
                                {{-- Card Top Row: Icon + Title + Status / Bulk Checkbox / Single Delete / Add Checkbox --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 transition-colors"
                                            :class="isSelected('{{ $mod['slug'] }}')
                                                     ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                                     : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/80 dark:border-indigo-900/40 group-hover:bg-indigo-100/70 dark:group-hover:bg-indigo-900/40'">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-black text-sm text-gray-900 dark:text-white truncate"
                                                title="{{ $mod['title'] }}">
                                                {{ $mod['title'] }}
                                            </h4>
                                            <span
                                                class="text-xs text-gray-400 dark:text-gray-500 block truncate mt-0.5"
                                                dir="ltr">
                                                    {{ $mod['slug'] }}
                                                </span>
                                        </div>
                                        </div>

                                        {{-- Top Right Status / Actions --}}
                                        <div class="shrink-0 pt-0.5">
                                            @if($alreadyInOrder)
                                                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                                    <label
                                                        class="flex items-center gap-1 cursor-pointer px-2 py-1 rounded-xl transition border select-none"
                                                        :class="isSelectedForRemoval('{{ $mod['slug'] }}') ? 'border-rose-500 bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:border-rose-300'"
                                                        title="انتخاب جهت حذف گروهی"
                                                        @click.stop="$event.stopPropagation()">
                                                        <input type="checkbox"
                                                               :checked="isSelectedForRemoval('{{ $mod['slug'] }}')"
                                                               @change="toggleRemoveSelection('module', '{{ $mod['slug'] }}', '{{ $mod['title'] }}')"
                                                               class="rounded text-rose-600 focus:ring-rose-500 border-gray-300 dark:border-gray-600 w-3.5 h-3.5 cursor-pointer">
                                                        <span class="text-xs font-bold">انتخاب</span>
                                                    </label>
                                                    <span
                                                        class="inline-flex items-center gap-1 text-xs font-black px-2 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50">
                                                        <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        فعال
                                                    </span>
                                                    <button type="button"
                                                            @click.stop="promptRemoveActiveItem('module', '{{ $mod['slug'] }}', '{{ $mod['title'] }}')"
                                                            class="p-1.5 rounded-xl text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200/70 dark:border-rose-800/50 transition cursor-pointer"
                                                            title="حذف این ماژول از سفارش">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                <div
                                                    class="w-6 h-6 rounded-xl transition-all flex items-center justify-center"
                                                    :class="isSelected('{{ $mod['slug'] }}')
                                                         ? 'bg-indigo-600 text-white shadow-xs'
                                                         : 'border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 group-hover:border-indigo-400'">
                                                    <svg x-show="isSelected('{{ $mod['slug'] }}')"
                                                         class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="3" style="display:none">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Module Description --}}
                                    @if($mod['description'])
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">
                                            {{ $mod['description'] }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400/80 dark:text-gray-500 italic">
                                            ماژول استاندارد توسعه امکانات سفارش
                                        </p>
                                    @endif

                                    {{-- Features Sub-Section with Toggle Button --}}
                                    @if(count($mod['features']) > 0)
                                        <div x-data="{ openFeatures: false }"
                                             class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2">
                                            {{-- Toggle Button for Hide/Unhide Features --}}
                                            <div class="flex items-center justify-between">
                                                <button type="button"
                                                        @click.stop="openFeatures = !openFeatures"
                                                        class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition cursor-pointer select-none">
                                                    <div
                                                        class="w-5 h-5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center transition-transform duration-200"
                                                        :class="openFeatures ? 'rotate-180 bg-indigo-100 dark:bg-indigo-900/60' : ''">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                    <span class="font-black"
                                                          x-text="openFeatures ? 'بستن قابلیت‌ها' : 'مشاهده قابلیت‌ها (' + '{{ reseller_fa_num(count($mod['features'])) }}' + ')'"></span>
                                                </button>

                                                <span
                                                    class="text-xs font-bold text-gray-400 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>{{ reseller_fa_num(count(array_filter($mod['features'], fn($f) => !empty($f['already_in_order'])))) }} فعال</span>
                                                </span>
                                            </div>

                                            {{-- Expandable Features List --}}
                                            <div x-show="openFeatures"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="space-y-1.5 max-h-52 overflow-y-auto pr-1 pt-1"
                                                 style="display: none;">
                                                @foreach($mod['features'] as $feat)
                                                    @php
                                                        $featAlreadyInOrder = !empty($feat['already_in_order']);
                                                    @endphp
                                                    <div
                                                        @if(!$featAlreadyInOrder)
                                                            @click.stop="toggleFeature({{ json_encode($feat) }}, '{{ $mod['slug'] }}')"
                                                        @endif
                                                        class="flex items-center justify-between px-3 py-2 rounded-xl border transition-all text-xs"
                                                        :class="[
                                                            {{ json_encode($featAlreadyInOrder) }}
                                                                ? (isSelectedForRemoval('{{ $feat['key'] }}')
                                                                    ? 'border-rose-400 bg-rose-50/70 dark:bg-rose-950/40 text-rose-900 dark:text-rose-300 font-bold'
                                                                    : 'border-emerald-200 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-300 font-bold')
                                                                : isSelected('{{ $feat['key'] }}')
                                                                    ? 'border-indigo-400 bg-indigo-100/70 dark:bg-indigo-900/50 text-indigo-950 dark:text-indigo-200 font-black cursor-pointer shadow-xs'
                                                                    : 'border-gray-200/70 dark:border-gray-700 bg-white dark:bg-gray-800/70 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300 font-bold cursor-pointer'
                                                        ]">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            @if($featAlreadyInOrder)
                                                                <input type="checkbox"
                                                                       :checked="isSelectedForRemoval('{{ $feat['key'] }}')"
                                                                       @change.stop="toggleRemoveSelection('feature', '{{ $feat['key'] }}', '{{ $feat['title'] }}')"
                                                                       class="rounded text-rose-600 focus:ring-rose-500 border-gray-300 dark:border-gray-600 w-3.5 h-3.5 cursor-pointer shrink-0"
                                                                       title="انتخاب جهت حذف گروهی">
                                                            @else
                                                                <div
                                                                    class="w-4 h-4 rounded-md border transition-all flex items-center justify-center shrink-0"
                                                                    :class="isSelected('{{ $feat['key'] }}')
                                                                         ? 'bg-indigo-600 border-indigo-600 text-white'
                                                                         : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'">
                                                                    <svg x-show="isSelected('{{ $feat['key'] }}')"
                                                                         class="w-2.5 h-2.5" fill="none"
                                                                         viewBox="0 0 24 24" stroke="currentColor"
                                                                         stroke-width="4" style="display:none">
                                                                        <path stroke-linecap="round"
                                                                              stroke-linejoin="round"
                                                                              d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                            <span class="truncate"
                                                                  title="{{ $feat['title'] }}">{{ $feat['title'] }}</span>
                                                        </div>

                                                        <div class="flex items-center gap-2 shrink-0">
                                                            @if($featAlreadyInOrder)
                                                                <span
                                                                    class="text-xs font-black text-emerald-600 dark:text-emerald-400">فعال</span>
                                                                <button type="button"
                                                                        @click.stop="promptRemoveActiveItem('feature', '{{ $feat['key'] }}', '{{ $feat['title'] }}')"
                                                                        class="p-1 rounded-lg text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-100/70 dark:hover:bg-rose-950/50 transition cursor-pointer"
                                                                        title="حذف این قابلیت از سفارش">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                         viewBox="0 0 24 24" stroke="currentColor"
                                                                         stroke-width="2">
                                                                        <path stroke-linecap="round"
                                                                              stroke-linejoin="round"
                                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                </button>
                                                            @else
                                                                @if($feat['cycle_price'] > 0)
                                                                    <span
                                                                        class="font-black tabular-nums text-indigo-600 dark:text-indigo-400 shrink-0 dir-ltr text-xs">
                                                                        +{{ reseller_fa_num(number_format($feat['cycle_price'])) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-xs text-gray-400 font-normal">رایگان</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Card Footer / Price Info --}}
                                <div
                                    class="px-5 py-3.5 bg-gray-50/70 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                    <div
                                        class="text-xs font-bold text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>تعرفه دوره:</span>
                                    </div>
                                    <div>
                                        @if($mod['cycle_price'] > 0)
                                            <div class="flex items-baseline gap-1 dir-ltr">
                                                <span
                                                    class="font-black text-sm sm:text-base text-gray-900 dark:text-white tabular-nums">
                                                    {{ reseller_fa_num(number_format($mod['cycle_price'])) }}
                                                </span>
                                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                                    {{ $currencyLabel }}
                                                </span>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-lg border border-indigo-200/50 dark:border-indigo-800/40">
                                                رایگان / شامل بسته
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- LEFT: Dedicated Sticky Invoice & Financial Studio Panel --}}
                <div class="lg:col-span-5 xl:col-span-4 sticky top-6 space-y-4">
                    <x-resellerhub::card
                        class="p-6 sm:p-7 border border-gray-200/90 dark:border-gray-800 shadow-md space-y-6">

                        {{-- Panel Header --}}
                        <div
                            class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-gray-900 dark:text-white">خلاصه اقلام و
                                        صورتحساب</h4>
                                    <p class="text-xs text-gray-400 mt-0.5">اقلام انتخابی جهت الحاق به سفارش</p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-xl text-xs font-black bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 tabular-nums"
                                x-text="toFa(selectedItems.length) + ' مورد'"></span>
                        </div>

                        {{-- Section 1: Selected Items List --}}
                        <div>
                            {{-- Empty State when no items selected --}}
                            <div x-show="selectedItems.length === 0"
                                 class="p-6 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 text-center space-y-2">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-gray-600 dark:text-gray-400 block">هیچ موردی انتخاب نشده است</span>
                                <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                                    جهت افزودن، ماژول‌ها یا امکانات دلخواه را از ستون روبرو انتخاب فرمایید.
                                </p>
                            </div>

                            {{-- Selected Items Scrollable List --}}
                            <div x-show="selectedItems.length > 0" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                <template x-for="item in selectedItems" :key="item.slug">
                                    <div
                                        class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-800 text-xs transition">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-2 h-2 rounded-full shrink-0"
                                                  :class="item.type === 'feature' ? 'bg-amber-500' : 'bg-indigo-500'"></span>
                                            <div class="min-w-0">
                                                <span class="font-bold text-gray-800 dark:text-gray-200 block truncate"
                                                      x-text="item.title"></span>
                                                <span class="text-xs text-gray-400 block"
                                                      x-text="item.type === 'feature' ? 'قابلیت' : 'ماژول'"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            <span
                                                class="font-black text-xs text-gray-900 dark:text-white tabular-nums dir-ltr"
                                                x-text="item.price > 0 ? (toFa(item.price.toLocaleString('en-US')) + ' {{ $currencyLabel }}') : 'رایگان'"></span>
                                            <button @click.stop="removeItem(item.slug)" type="button"
                                                    class="text-gray-400 hover:text-rose-500 dark:hover:text-rose-400 transition-colors p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer"
                                                    title="حذف از انتخاب‌ها">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Section 2: Invoice Issuance Toggle Card --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-3">
                            <div>
                                <span
                                    class="text-xs font-black text-gray-800 dark:text-gray-200 block">صدور فاکتور جدید</span>
                                <p class="text-xs text-gray-400 mt-0.5">ایجاد فاکتور مجزا برای پرداخت مشتری</p>
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer select-none shrink-0 group">
                                <span class="text-xs font-black transition-colors"
                                      :class="issueInvoice ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'"
                                      x-text="issueInvoice ? 'فاکتور صادر شود' : 'عدم صدور'"></span>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" x-model="issueInvoice" class="sr-only peer">
                                    <div
                                        class="w-13 h-7 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors duration-300 shadow-inner"></div>
                                    <div
                                        class="absolute right-1 top-1 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center">
                                        <svg
                                            class="w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Section 3: Financial Summary Rows --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-bold">مجموع تعرفه اقلام:</span>
                                <div
                                    class="flex items-baseline gap-1 dir-ltr font-black text-gray-900 dark:text-white tabular-nums">
                                    <span x-text="formatTotal()"></span>
                                    <span class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-bold">وضعیت مالی الحاق:</span>
                                <span class="font-bold text-xs"
                                      :class="issueInvoice ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400'"
                                      x-text="issueInvoice ? 'صدور فاکتور در انتظار پرداخت' : 'اعمال بدون فاکتور (رایگان)'"></span>
                            </div>

                            <div
                                class="border-t border-gray-200/80 dark:border-gray-700/60 pt-2.5 flex items-center justify-between">
                                <span class="font-black text-xs sm:text-sm text-gray-900 dark:text-white"
                                      x-text="issueInvoice ? 'مبلغ قابل پرداخت فاکتور:' : 'مبلغ فاکتور صادرشده:'"></span>
                                <div class="flex items-baseline gap-1 dir-ltr">
                                    <span class="font-black text-lg sm:text-xl tabular-nums"
                                          :class="issueInvoice ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'"
                                          x-text="issueInvoice ? formatTotal() : '۰'"></span>
                                    <span class="text-xs font-bold text-gray-400">{{ $currencyLabel }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Action Button --}}
                        <button type="button"
                                @click="submitItems('{{ route("reseller.orders.add-items", $order->id) }}')"
                                :disabled="!selectedItems.length || submitting"
                                class="w-full py-4 px-6 rounded-2xl text-white font-black text-sm shadow-xl transition-all flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed select-none active:scale-[0.99]"
                                :class="issueInvoice
                                    ? 'bg-gradient-to-r from-indigo-600 via-indigo-700 to-indigo-800 hover:from-indigo-500 hover:to-indigo-700 shadow-indigo-600/25'
                                    : 'bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 shadow-teal-600/25'">
                            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                                 style="display:none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="!submitting && issueInvoice" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <svg x-show="!submitting && !issueInvoice" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span
                                x-text="submitting ? (issueInvoice ? 'در حال صدور فاکتور...' : 'در حال اعمال به سفارش...') : (issueInvoice ? 'صدور فاکتور و الحاق اقلام' : 'تایید و اعمال مستقیم به سفارش')"></span>
                        </button>

                        <div class="flex items-center justify-center gap-1.5 text-xs text-gray-400 text-center">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>تغییرات لایسنس بلافاصله پس از ثبت نهایی اعمال خواهد شد.</span>
                        </div>
                    </x-resellerhub::card>
                </div>
            </div>

        {{-- Modal for Confirming Removal of Active Module/Feature (Single or Bulk) --}}
        <div x-show="removeModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
                     @click="!removing && (removeModalOpen = false)"></div>

                <div
                    class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-7 space-y-5 text-right">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-black text-gray-900 dark:text-white"
                                x-text="itemsToRemove.length > 1 ? ('حذف گروهی ' + toFa(itemsToRemove.length) + ' مورد') : ('حذف ' + (itemsToRemove[0]?.type === 'module' ? 'ماژول' : 'قابلیت'))"></h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                               x-text="itemsToRemove.length === 1 ? ('«' + (itemsToRemove[0]?.title || '') + '»') : 'بررسی و تایید نهایی موارد انتخابی'"></p>
                        </div>
                    </div>

                    {{-- Multi items preview --}}
                    <div x-show="itemsToRemove.length > 1" class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                        <template x-for="item in itemsToRemove" :key="item.slug">
                            <div
                                class="flex items-center justify-between p-2 rounded-xl bg-gray-50 dark:bg-gray-800/80 text-xs border border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200"
                                          x-text="item.title"></span>
                                </div>
                                <span class="text-xs text-gray-400"
                                      x-text="item.type === 'module' ? 'ماژول' : 'قابلیت'"></span>
                            </div>
                        </template>
                    </div>

                    <div
                        class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 text-xs text-rose-800 dark:text-rose-300 leading-relaxed">
                        هشدار: در صورت تایید، دسترسی موارد فوق بلافاصله از سفارش و سامانه متصل به آن لغو خواهد شد. آیا
                        از این عملیات مطمئن هستید؟
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                @click="removeModalOpen = false"
                                :disabled="removing"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer disabled:opacity-50">
                            انصراف
                        </button>
                        <button type="button"
                                @click="confirmRemoveActiveItems('{{ route("reseller.orders.remove-item", $order->id) }}')"
                                :disabled="removing"
                                class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black shadow-lg shadow-rose-600/25 transition cursor-pointer disabled:opacity-50">
                            <svg x-show="removing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"
                                 style="display:none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span
                                x-text="removing ? 'در حال حذف...' : (itemsToRemove.length > 1 ? 'تایید و حذف گروهی' : 'تایید و حذف از سفارش')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
