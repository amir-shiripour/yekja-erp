@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'انتخاب کنید...',
    'searchPlaceholder' => 'جستجو در گزینه‌ها...',
    'searchable' => true,
    'multiple' => false,
    'clearable' => true,
    'allLabel' => null,
    'allValue' => '__all__',
    'color' => 'indigo', // indigo, emerald, blue, amber, rose, gray
    'variant' => 'input', // 'input' or 'card'
    'cardTitle' => null,
    'cardSubtitle' => null,
    'emptyText' => 'موردی یافت نشد.',
    'maxHeight' => 'max-h-60',
    'required' => false,
    'disabled' => false,
    'model' => null,
    'filterKey' => null,
    'placement' => 'bottom', // 'bottom' or 'top'
    'size' => 'default', // 'default' or 'sm'
    'rounded' => 'xl', // 'xl' or 'full'
])

@php
    $normalizedOptions = [];
    $rawOptions = is_iterable($options) ? $options : [];

    foreach ($rawOptions as $key => $opt) {
        if (is_array($opt)) {
            $val = (string) ($opt['value'] ?? $opt['id'] ?? $key);
            $lbl = (string) ($opt['label'] ?? $opt['name'] ?? $opt['title'] ?? $val);
            $initial = (string) ($opt['initial'] ?? (mb_strlen($lbl) > 0 ? mb_substr(trim($lbl), 0, 1) : '•'));
            $subtitle = $opt['subtitle'] ?? $opt['email'] ?? $opt['phone'] ?? $opt['mobile'] ?? null;
            $badge = $opt['badge'] ?? $opt['status_name'] ?? (isset($opt['case_number']) && $opt['case_number'] ? 'پرونده: ' . $opt['case_number'] : null);
            $roleIds = $opt['role_ids'] ?? [];
            $statusId = $opt['status_id'] ?? null;

            $normalizedOptions[] = [
                'value'    => $val,
                'label'    => $lbl,
                'initial'  => $initial,
                'subtitle' => $subtitle,
                'badge'    => $badge,
                'role_ids' => array_map('strval', (array) $roleIds),
                'status_id'=> $statusId !== null ? (string) $statusId : null,
                'email'    => $opt['email'] ?? null,
                'phone'    => $opt['phone'] ?? $opt['mobile'] ?? null,
                'company'  => $opt['company'] ?? null,
            ];
        } elseif (is_object($opt)) {
            $val = (string) ($opt->value ?? $opt->id ?? $key);
            $lbl = (string) ($opt->label ?? $opt->name ?? $opt->title ?? $val);
            $initial = (string) ($opt->initial ?? (mb_strlen($lbl) > 0 ? mb_substr(trim($lbl), 0, 1) : '•'));
            $subtitle = $opt->subtitle ?? $opt->email ?? $opt->phone ?? $opt->mobile ?? null;
            $badge = $opt->badge ?? null;

            $normalizedOptions[] = [
                'value'    => $val,
                'label'    => $lbl,
                'initial'  => $initial,
                'subtitle' => $subtitle,
                'badge'    => $badge,
                'role_ids' => [],
                'status_id'=> null,
                'email'    => $opt->email ?? null,
                'phone'    => $opt->phone ?? $opt->mobile ?? null,
                'company'  => $opt->company ?? null,
            ];
        } else {
            $val = (string) $key;
            $lbl = (string) $opt;
            $initial = mb_strlen($lbl) > 0 ? mb_substr(trim($lbl), 0, 1) : '•';
            $normalizedOptions[] = [
                'value'    => $val,
                'label'    => $lbl,
                'initial'  => $initial,
                'subtitle' => null,
                'badge'    => null,
                'role_ids' => [],
                'status_id'=> null,
                'email'    => null,
                'phone'    => null,
                'company'  => null,
            ];
        }
    }

    $rawSelected = old($name, $value);
    if ($multiple) {
        $initialSelected = is_array($rawSelected) ? array_map('strval', $rawSelected) : ($rawSelected !== null && $rawSelected !== '' ? [(string) $rawSelected] : []);
    } else {
        $initialSelected = is_array($rawSelected) ? (string) ($rawSelected[0] ?? '') : (string) ($rawSelected ?? '');
    }

    $theme = match($color) {
        'emerald' => [
            'ring'         => 'focus-within:ring-4 focus-within:ring-emerald-500/10 focus-within:border-emerald-500 hover:border-emerald-400',
            'border'       => 'border-emerald-500',
            'bg_light'     => 'bg-emerald-50/40 dark:bg-emerald-950/20',
            'border_light' => 'border-emerald-100 dark:border-emerald-800/40',
            'badge'        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 border-emerald-200/60 dark:border-emerald-800/60',
            'avatar_bg'    => 'bg-emerald-600 text-white shadow-emerald-500/20',
            'avatar_light' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300',
            'text'         => 'text-emerald-600 dark:text-emerald-400',
            'item_hover'   => 'hover:bg-emerald-50/70 dark:hover:bg-emerald-950/40',
            'item_active'  => 'bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300',
            'btn'          => 'border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40',
        ],
        'blue' => [
            'ring'         => 'focus-within:ring-4 focus-within:ring-blue-500/10 focus-within:border-blue-500 hover:border-blue-400',
            'border'       => 'border-blue-500',
            'bg_light'     => 'bg-blue-50/40 dark:bg-blue-950/20',
            'border_light' => 'border-blue-100 dark:border-blue-800/40',
            'badge'        => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 border-blue-200/60 dark:border-blue-800/60',
            'avatar_bg'    => 'bg-blue-600 text-white shadow-blue-500/20',
            'avatar_light' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300',
            'text'         => 'text-blue-600 dark:text-blue-400',
            'item_hover'   => 'hover:bg-blue-50/70 dark:hover:bg-blue-950/40',
            'item_active'  => 'bg-blue-50/60 dark:bg-blue-950/30 text-blue-700 dark:text-blue-300',
            'btn'          => 'border-blue-200 dark:border-blue-800/60 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950/40',
        ],
        'amber' => [
            'ring'         => 'focus-within:ring-4 focus-within:ring-amber-500/10 focus-within:border-amber-500 hover:border-amber-400',
            'border'       => 'border-amber-500',
            'bg_light'     => 'bg-amber-50/40 dark:bg-amber-950/20',
            'border_light' => 'border-amber-100 dark:border-amber-800/40',
            'badge'        => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border-amber-200/60 dark:border-amber-800/60',
            'avatar_bg'    => 'bg-amber-600 text-white shadow-amber-500/20',
            'avatar_light' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300',
            'text'         => 'text-amber-600 dark:text-amber-400',
            'item_hover'   => 'hover:bg-amber-50/70 dark:hover:bg-amber-950/40',
            'item_active'  => 'bg-amber-50/60 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300',
            'btn'          => 'border-amber-200 dark:border-amber-800/60 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/40',
        ],
        default => [ // indigo
            'ring'         => 'focus-within:ring-4 focus-within:ring-indigo-500/10 focus-within:border-indigo-500 hover:border-indigo-400',
            'border'       => 'border-indigo-500',
            'bg_light'     => 'bg-indigo-50/40 dark:bg-indigo-950/20',
            'border_light' => 'border-indigo-100 dark:border-indigo-800/40',
            'badge'        => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200 border-indigo-200/60 dark:border-indigo-800/60',
            'avatar_bg'    => 'bg-indigo-600 text-white shadow-indigo-500/20',
            'avatar_light' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300',
            'text'         => 'text-indigo-600 dark:text-indigo-400',
            'item_hover'   => 'hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40',
            'item_active'  => 'bg-indigo-50/60 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-300',
            'btn'          => 'border-indigo-200 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40',
        ],
    };

    $inputBaseName = $name;
    if ($multiple && $name && !str_ends_with($name, '[]')) {
        $inputMultiName = $name . '[]';
    } else {
        $inputMultiName = $name;
    }
@endphp

@once
    <script>
        if (typeof window.uiSearchableSelect === 'undefined') {
            window.uiSearchableSelect = function (cfg) {
                return {
                    name: cfg.name || '',
                    multiple: !!cfg.multiple,
                    open: false,
                    search: '',
                    searchable: cfg.searchable !== false,
                    allLabel: cfg.allLabel || null,
                    allValue: cfg.allValue || '__all__',
                    options: cfg.options || [],
                    selected: cfg.initialSelected,
                    placeholder: cfg.placeholder || 'انتخاب کنید...',
                    cardTitle: cfg.cardTitle || '',
                    cardSubtitle: cfg.cardSubtitle || '',
                    placement: cfg.placement || 'bottom',

                    init() {
                        if (this.multiple && !Array.isArray(this.selected)) {
                            this.selected = (this.selected !== '' && this.selected !== null && this.selected !== undefined)
                                ? [String(this.selected)]
                                : [];
                        } else if (!this.multiple) {
                            this.selected = (this.selected !== null && this.selected !== undefined)
                                ? String(this.selected)
                                : '';
                        }
                        this.dispatchChange();
                    },

                    isSelected(val) {
                        val = String(val);
                        if (this.multiple) {
                            return Array.isArray(this.selected) && this.selected.includes(val);
                        }
                        return String(this.selected) === val;
                    },

                    select(val) {
                        val = String(val);
                        if (this.multiple) {
                            this.toggle(val);
                        } else {
                            this.selected = val;
                            this.search = '';
                            this.open = false;
                            this.dispatchChange();
                        }
                    },

                    toggle(val) {
                        val = String(val);
                        if (!this.multiple) {
                            this.select(val);
                            return;
                        }
                        if (!Array.isArray(this.selected)) this.selected = [];

                        if (val === this.allValue) {
                            if (this.isSelected(this.allValue)) {
                                this.selected = [];
                            } else {
                                this.selected = [this.allValue];
                            }
                        } else {
                            this.selected = this.selected.filter(v => v !== this.allValue);
                            if (this.isSelected(val)) {
                                this.selected = this.selected.filter(v => v !== val);
                            } else {
                                this.selected.push(val);
                            }
                        }
                        this.dispatchChange();
                    },

                    clear() {
                        this.selected = this.multiple ? [] : '';
                        this.search = '';
                        this.dispatchChange();
                    },

                    dispatchChange() {
                        if (this.name) {
                            this.$dispatch(this.name + '-changed', this.selected);
                            this.$dispatch('input', this.selected);
                            this.$dispatch('change', this.selected);
                        }
                    },

                    get firstSelected() {
                        if (this.multiple) {
                            const firstVal = Array.isArray(this.selected) && this.selected.length ? this.selected[0] : null;
                            if (!firstVal) return null;
                            return this.options.find(o => String(o.value) === String(firstVal)) || null;
                        }
                        return this.options.find(o => String(o.value) === String(this.selected)) || null;
                    },

                    get selectedItems() {
                        if (!this.multiple || !Array.isArray(this.selected)) return [];
                        if (this.allLabel && this.isSelected(this.allValue)) {
                            return [{value: this.allValue, label: this.allLabel}];
                        }
                        return this.options.filter(o => this.selected.includes(String(o.value)));
                    },

                    get selectedCount() {
                        if (this.multiple) {
                            return Array.isArray(this.selected) ? this.selected.length : 0;
                        }
                        return (this.selected !== '' && this.selected !== null && this.selected !== undefined) ? 1 : 0;
                    },

                    filteredOptions() {
                        const term = (this.search || '').trim().toLowerCase();
                        let list = this.options || [];

                        const ctx = window.taskFormContext || {};
                        if (this.name === 'related_user_ids' && ctx.users && ctx.selectedRelatedUserRoleIds) {
                            const selectedRoles = (ctx.selectedRelatedUserRoleIds || []).filter(v => v !== '__all__').map(String);
                            if (selectedRoles.length > 0) {
                                const allUsers = ctx.users || [];
                                list = list.filter(opt => {
                                    const user = allUsers.find(u => String(u.id) === String(opt.value));
                                    if (!user) return false;
                                    const roleIds = (user.role_ids || []).map(String);
                                    return selectedRoles.some(rid => roleIds.includes(rid));
                                });
                            }
                        } else if (this.name === 'related_client_ids' && ctx.clients && ctx.selectedRelatedClientStatusIds) {
                            const selectedStatuses = (ctx.selectedRelatedClientStatusIds || []).filter(v => v !== '__all__').map(String);
                            if (selectedStatuses.length > 0) {
                                const allClients = ctx.clients || [];
                                list = list.filter(opt => {
                                    const client = allClients.find(c => String(c.id) === String(opt.value));
                                    if (!client) return false;
                                    return selectedStatuses.includes(String(client.status_id));
                                });
                            }
                        }

                        if (!term) return list;
                        return list.filter(o =>
                            (o.label || '').toLowerCase().includes(term) ||
                            (o.subtitle || '').toLowerCase().includes(term) ||
                            (o.phone || '').toLowerCase().includes(term) ||
                            (o.email || '').toLowerCase().includes(term) ||
                            (o.badge || '').toLowerCase().includes(term) ||
                            (o.company || '').toLowerCase().includes(term)
                        );
                    }
                };
            };
        }
    </script>
@endonce

<div
    x-data="window.uiSearchableSelect({
        name: {{ json_encode((string) $name, JSON_UNESCAPED_UNICODE) }},
        multiple: @js($multiple),
        options: {{ json_encode($normalizedOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }},
        initialSelected: {{ json_encode($initialSelected, JSON_UNESCAPED_UNICODE) }},
        searchable: @js($searchable),
        allLabel: {{ json_encode($allLabel, JSON_UNESCAPED_UNICODE) }},
        allValue: {{ json_encode((string) $allValue, JSON_UNESCAPED_UNICODE) }},
        placeholder: {{ json_encode((string) $placeholder, JSON_UNESCAPED_UNICODE) }},
        cardTitle: {{ json_encode((string) $cardTitle, JSON_UNESCAPED_UNICODE) }},
        cardSubtitle: {{ json_encode((string) $cardSubtitle, JSON_UNESCAPED_UNICODE) }},
        placement: @js($placement),
    })"
    class="relative w-full {{ $label ? 'space-y-1.5' : '' }}"
    :class="open ? 'z-50' : ''"
    {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'options', 'value', 'placeholder', 'multiple', 'variant', 'color']) }}
>
    {{-- فیلدهای ارسالی مخفی فرم (Hidden inputs) --}}
    @if($name)
        @if($multiple)
            <template x-for="val in selected" :key="val">
                <input type="hidden" name="{{ $inputMultiName }}" :value="val">
            </template>
        @else
            <input type="hidden" name="{{ $name }}" :value="selected">
        @endif
    @endif

    {{-- برچسب ورودی در صورت تعیین --}}
    @if($label)
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    {{-- ۱. حالت نمایش کارتی پیشرفته (مشابه بخش پیگیری) --}}
    @if($variant === 'card')
        <div class="relative">
            {{-- کارت زمانی که گزینه انتخاب شده است --}}
            <template x-if="firstSelected">
                <div
                    class="p-4 rounded-2xl {{ $theme['bg_light'] }} border {{ $theme['border_light'] }} transition-all shadow-2xs space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-11 h-11 rounded-xl {{ $theme['avatar_bg'] }} flex items-center justify-center font-black text-sm shadow-md shrink-0">
                                <span x-text="firstSelected.initial"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs sm:text-sm font-black text-gray-900 dark:text-white truncate"
                                          x-text="firstSelected.label"></span>
                                    <template x-if="firstSelected.badge">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $theme['badge'] }}"
                                            x-text="firstSelected.badge"></span>
                                    </template>
                                </div>
                                <span class="text-[11px] text-gray-400 font-bold block mt-0.5"
                                      x-text="cardSubtitle || 'مورد انتخاب‌شده'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    @click="open = !open; if(open && searchable) $nextTick(() => $refs.searchInput && $refs.searchInput.focus())"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border {{ $theme['btn'] }} text-xs font-bold transition-all shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                                <span>تغییر</span>
                            </button>
                            @if($clearable)
                                <button type="button"
                                        @click="clear()"
                                        class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-rose-600 hover:border-rose-200 transition-colors cursor-pointer"
                                        title="حذف انتخاب">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- اطلاعات تماس/فرعی در کارت --}}
                    <template x-if="firstSelected.email || firstSelected.phone || firstSelected.company">
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2.5 border-t border-gray-100/80 dark:border-gray-800/40 text-xs">
                            <template x-if="firstSelected.phone">
                                <div
                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-gray-100/70 dark:border-gray-900/40 min-w-0">
                                    <span class="text-gray-400 font-bold shrink-0">تلفن:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr text-right truncate"
                                          x-text="firstSelected.phone"></span>
                                </div>
                            </template>
                            <template x-if="firstSelected.email">
                                <div
                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-gray-100/70 dark:border-gray-900/40 min-w-0">
                                    <span class="text-gray-400 font-bold shrink-0">ایمیل:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr truncate"
                                          x-text="firstSelected.email"></span>
                                </div>
                            </template>
                            <template x-if="firstSelected.company">
                                <div
                                    class="flex items-center gap-1.5 p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-gray-100/70 dark:border-gray-900/40 min-w-0 sm:col-span-2">
                                    <span class="text-gray-400 font-bold shrink-0">شرکت / سازمان:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate"
                                          x-text="firstSelected.company"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            {{-- دکمه خالی کارتی زمانی که هیچ موردی انتخاب نشده --}}
            <template x-if="!firstSelected">
                <button type="button"
                        @click="open = !open; if(open && searchable) $nextTick(() => $refs.searchInput && $refs.searchInput.focus())"
                        class="w-full p-4 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between gap-3 text-right transition-all cursor-pointer group">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 group-hover:bg-indigo-100 group-hover:text-indigo-700 dark:group-hover:bg-indigo-950/60 dark:group-hover:text-indigo-300 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <span
                                class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors"
                                x-text="cardTitle || placeholder"></span>
                            <span class="block text-[11px] text-gray-400"
                                  x-text="cardSubtitle || 'کلیک برای جستجو و انتخاب'"></span>
                        </div>
                    </div>
                    <span
                        class="text-xs font-bold {{ $theme['text'] }} group-hover:translate-x-[-2px] transition-transform">
                        انتخاب &larr;
                    </span>
                </button>
            </template>
        </div>
    @else
        {{-- ۲. حالت ورودی فرم مدرن (Input-Style Trigger) --}}
        @php
            $triggerSizeClasses = match($size) {
                'sm' => 'h-9 px-3 text-xs',
                default => 'min-h-[42px] px-3.5 py-2 text-xs sm:text-sm',
            };
            $triggerRadiusClass = match($rounded) {
                'full' => 'rounded-full',
                default => 'rounded-xl',
            };
        @endphp
        <div
            @click="open = !open; if(open && searchable) $nextTick(() => $refs.searchInput && $refs.searchInput.focus())"
            class="w-full {{ $triggerSizeClasses }} {{ $triggerRadiusClass }} border border-gray-200/80 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700/60 flex items-center justify-between gap-2 cursor-pointer transition-all {{ $theme['ring'] }} select-none"
        >
            {{-- بخش محتوای انتخاب‌شده --}}
            <div class="flex items-center gap-2 flex-1 min-w-0 flex-wrap">
                @if($multiple)
                    {{-- حالت چندانتخابی --}}
                    <template x-if="selectedCount === 0">
                        <span class="text-xs sm:text-sm font-medium text-gray-400 dark:text-gray-500"
                              x-text="placeholder"></span>
                    </template>
                    <template x-for="item in selectedItems" :key="item.value">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $theme['badge'] }}">
                            <span x-text="item.label"></span>
                            <button type="button"
                                    @click.stop="toggle(item.value)"
                                    class="p-0.5 rounded-full hover:bg-black/10 dark:hover:bg-white/20 transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </template>
                @else
                    {{-- حالت تک انتخابی --}}
                    <template x-if="firstSelected">
                        <div class="flex items-center gap-2 min-w-0">
                            <template x-if="firstSelected.initial && '{{ $size }}' !== 'sm'">
                                <span
                                    class="w-6 h-6 rounded-md {{ $theme['avatar_light'] }} flex items-center justify-center font-bold text-[10px] shrink-0"
                                    x-text="firstSelected.initial"></span>
                            </template>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate"
                                  x-text="firstSelected.label"></span>
                            <template x-if="firstSelected.badge">
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"
                                    x-text="firstSelected.badge"></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="!firstSelected">
                        <span class="text-xs sm:text-sm font-medium text-gray-400 dark:text-gray-500"
                              x-text="placeholder"></span>
                    </template>
                @endif
            </div>

            {{-- آیکون‌های کنترل سمت چپ --}}
            <div class="flex items-center gap-1.5 shrink-0 text-gray-400">
                @if($clearable)
                    <template x-if="selectedCount > 0">
                        <button type="button"
                                @click.stop="clear()"
                                class="p-1 rounded-md hover:bg-gray-200/60 dark:hover:bg-gray-700/60 text-gray-400 hover:text-rose-500 transition-colors"
                                title="پاک کردن">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </template>
                @endif
                <svg class="w-4 h-4 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    @endif

    {{-- ۳. منوی بازشونده شناور (Floating Dropdown Panel) --}}
    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute z-50 w-full min-w-[12rem] right-0 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl overflow-hidden"
        :class="placement === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
    >
        {{-- اینپوت جستجو --}}
        @if($searchable)
            <div class="p-2.5 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30">
                <div class="relative">
                    <input
                        type="text"
                        x-ref="searchInput"
                        x-model="search"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full rounded-xl border border-gray-200/80 bg-white dark:bg-gray-900 dark:border-gray-700 pr-8 pl-3 py-2 text-xs text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-hidden"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-2.5 pointer-events-none" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        @endif

        {{-- فهرست گزینه‌ها --}}
        <ul class="{{ $maxHeight }} overflow-auto text-xs py-1 divide-y divide-gray-50 dark:divide-gray-700/30">
            {{-- گزینه "همه" در صورت وجود allLabel --}}
            @if($allLabel)
                <li>
                    <button
                        type="button"
                        @click="toggle('{{ $allValue }}')"
                        class="w-full text-right px-4 py-2.5 {{ $theme['item_hover'] }} flex items-center justify-between gap-3 transition-colors cursor-pointer"
                        :class="isSelected('{{ $allValue }}') ? '{{ $theme['item_active'] }} font-bold' : ''"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span
                                class="w-8 h-8 rounded-lg {{ $theme['avatar_light'] }} flex items-center justify-center font-bold text-xs shrink-0">★</span>
                            <span class="truncate text-gray-900 dark:text-white font-bold">{{ $allLabel }}</span>
                        </div>
                        <span x-show="isSelected('{{ $allValue }}')"
                              class="{{ $theme['text'] }} shrink-0 font-black">✓</span>
                    </button>
                </li>
            @endif

            {{-- گزینه‌های داینامیک --}}
            <template x-for="opt in filteredOptions()" :key="opt.value">
                <li>
                    <button
                        type="button"
                        @click="select(opt.value)"
                        class="w-full text-right px-4 py-2.5 {{ $theme['item_hover'] }} flex items-center justify-between gap-3 transition-colors cursor-pointer"
                        :class="isSelected(opt.value) ? '{{ $theme['item_active'] }} font-bold' : ''"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <template x-if="opt.initial">
                                <span
                                    class="w-8 h-8 rounded-lg {{ $theme['avatar_light'] }} flex items-center justify-center font-bold text-xs shrink-0"
                                    x-text="opt.initial"></span>
                            </template>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="block truncate text-gray-900 dark:text-white font-bold"
                                          x-text="opt.label"></span>
                                    <template x-if="opt.badge">
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                            x-text="opt.badge"></span>
                                    </template>
                                </div>
                                <template x-if="opt.subtitle">
                                    <span class="block text-[10px] text-gray-400 dir-ltr text-right truncate"
                                          x-text="opt.subtitle"></span>
                                </template>
                            </div>
                        </div>
                        <span x-show="isSelected(opt.value)" class="{{ $theme['text'] }} shrink-0 font-black">✓</span>
                    </button>
                </li>
            </template>

            {{-- پیام در صورت خالی بودن نتیجه جستجو --}}
            <template x-if="filteredOptions().length === 0">
                <li class="px-4 py-4 text-xs text-gray-400 text-center">
                    {{ $emptyText }}
                </li>
            </template>
        </ul>
    </div>
</div>
