<x-resellerhub::card class="p-6 sm:p-8 space-y-6">
    <div class="flex items-center gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
        {{-- Live Badge Preview --}}
        <span
            class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-500/20 transition-all duration-300 shrink-0"
            :style="`background-color: ${getColorHex(selectedColor)}; color: #ffffff;`"
            x-html="renderIcon(selectedIcon, 'w-6 h-6')">
                    </span>
        <div>
            <h2 class="text-base font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.packages.form.card_basic_title') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('resellerhub::messages.packages.form.card_basic_desc') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Package Name --}}
        <div class="md:col-span-2">
            <x-resellerhub::label>
                {{ __('resellerhub::messages.packages.form.name_label') }} <span class="text-red-500 font-bold">*</span>
            </x-resellerhub::label>
            <x-resellerhub::input type="text" name="name" x-model="packageName" required
                                  placeholder="{{ __('resellerhub::messages.packages.form.name_placeholder') }}"
            />
            <p class="text-xs text-gray-400 mt-1 ms-1">{{ __('resellerhub::messages.packages.form.name_hint') }}</p>
        </div>

        {{-- Status Switch (Active / Inactive) --}}
        <div class="md:col-span-2 p-4 rounded-2xl border transition-colors"
             :class="isActive ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/60' : 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/60'">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                      :class="isActive ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'">
                                    <template x-if="isActive">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </template>
                                    <template x-if="!isActive">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </template>
                                </span>
                    <div>
                        <div
                            class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span
                                x-text="isActive ? '{{ __('resellerhub::messages.packages.form.status_active') }}' : '{{ __('resellerhub::messages.packages.form.status_inactive') }}'"></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                                  :class="isActive ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'"
                                  x-text="isActive ? '{{ __('resellerhub::messages.packages.form.status_active_badge') }}' : '{{ __('resellerhub::messages.packages.form.status_inactive_badge') }}'"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                            {{ __('resellerhub::messages.packages.form.status_desc') }}
                        </p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="hidden" name="is_active" :value="isActive ? 1 : 0">
                    <input type="checkbox" x-model="isActive" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                </label>
            </div>
        </div>

        {{-- Color Picker --}}
        <div>
            <label
                class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">{{ __('resellerhub::messages.packages.form.color_label') }}</label>
            <div class="flex items-center gap-2 flex-wrap">
                <template x-for="c in availableColors" :key="c.key">
                    <button type="button" @click="setColor(c.hex)"
                            :title="c.label"
                            :class="getColorHex(selectedColor) === c.hex ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' : 'opacity-70 hover:opacity-100'"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full transition-all cursor-pointer shadow-xs flex items-center justify-center text-white text-xs"
                            :style="`background-color: ${c.hex}`">
                        <template x-if="getColorHex(selectedColor) === c.hex">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                    </button>
                </template>

                {{-- Custom Hex / Native Color Picker --}}
                <div
                    class="flex items-center gap-1.5 ms-1 p-1 rounded-xl bg-gray-100 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600">
                    <input type="color"
                           :value="getColorHex(selectedColor)"
                           @input="selectedColor = $event.target.value"
                           class="w-6 h-6 rounded-lg cursor-pointer border-0 p-0 bg-transparent">
                    <input type="text"
                           x-model="selectedColor"
                           dir="ltr"
                           placeholder="{{ $defaultColor }}"
                           class="w-20 text-xs font-bold text-center bg-transparent border-0 focus:ring-0 p-0 text-gray-800 dark:text-gray-200">
                </div>
            </div>
            <input type="hidden" name="color" :value="getColorHex(selectedColor)">
        </div>

        {{-- Industry Icon Picker --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label
                    class="block text-xs font-bold text-gray-700 dark:text-gray-300">{{ __('resellerhub::messages.packages.form.icon_label') }}</label>
                <span
                    class="text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2 py-0.5 rounded-md"
                    x-text="getSelectedIconLabel()"></span>
            </div>
            <div
                class="flex items-center gap-1.5 flex-wrap max-h-44 overflow-y-auto p-2 bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/60">
                <template x-for="ic in availableIcons" :key="ic.key">
                    <button type="button" @click="selectedIcon = ic.key"
                            :title="ic.label"
                            :class="selectedIcon === ic.key ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25 ring-2 ring-indigo-500/30 scale-105' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/80 dark:border-gray-700'"
                            class="w-9 h-9 rounded-xl transition-all cursor-pointer flex items-center justify-center shrink-0"
                            x-html="renderIcon(ic.key, 'w-4 h-4')">
                    </button>
                </template>
            </div>
            <p class="text-xs text-gray-400 mt-1 ms-1">{{ __('resellerhub::messages.packages.form.icon_hint') }}</p>
        </div>

        {{-- Description --}}
        <div class="md:col-span-2">
            <x-resellerhub::label>
                {{ __('resellerhub::messages.packages.form.desc_label') }}
            </x-resellerhub::label>
            <x-resellerhub::textarea class="resize-none" name="description" rows="2"
                                     placeholder="{{ __('resellerhub::messages.packages.form.desc_placeholder') }}"
            >{{ old('description') }}</x-resellerhub::textarea>
        </div>
    </div>
</x-resellerhub::card>
