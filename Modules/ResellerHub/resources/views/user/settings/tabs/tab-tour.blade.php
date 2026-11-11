<div role="tabpanel" id="tabpanel-tour" aria-labelledby="tab-tour" x-show="activeTab === 'tour'" x-cloak
     class="space-y-6">

    <style>
        /*noinspection CssUnusedSymbol*/
        .tour-sortable-ghost {
            opacity: 0.35 !important;
            background: rgba(99, 102, 241, 0.08) !important;
            border: 2px dashed #6366f1 !important;
            border-radius: 1rem !important;
            transform: scale(0.99);
        }

        /*noinspection CssUnusedSymbol*/
        .tour-sortable-chosen {
            border-color: #6366f1 !important;
        }

        /*noinspection CssUnusedSymbol*/
        .tour-sortable-drag {
            cursor: grabbing !important;
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.25), 0 8px 10px -6px rgba(99, 102, 241, 0.25) !important;
            background-color: #ffffff !important;
            border-color: #4f46e5 !important;
            transform: rotate(-1deg) scale(1.02);
            z-index: 9999 !important;
        }

        /*noinspection CssUnusedSymbol*/
        .dark .tour-sortable-drag {
            background-color: #1f2937 !important;
        }
    </style>

    {{-- Hidden JSON payloads for tour settings --}}
    <input type="hidden" name="onboarding_tour_enabled" value="0">
    <input type="hidden" name="onboarding_tour_display_mode" :value="tourDisplayMode || 'bento'">
    <input type="hidden" name="onboarding_tour_enforcement" :value="tourEnforcement || 'soft'">
    <input type="hidden" name="onboarding_tour_grace_period_days" :value="tourGracePeriodDays || 3">
    <input type="hidden" name="onboarding_tour_celebration_title" :value="tourCelebrationTitle || ''">
    <input type="hidden" name="onboarding_tour_celebration_message" :value="tourCelebrationMessage || ''">
    <input type="hidden" name="onboarding_tour_steps" :value="JSON.stringify(tourSteps)">
    <input type="hidden" name="onboarding_tour_optional_reminder_enabled"
           :value="tourOptionalReminderEnabled ? '1' : '0'">
    <input type="hidden" name="onboarding_tour_optional_reminder_interval_days"
           :value="tourOptionalReminderIntervalDays">
    <input type="hidden" name="onboarding_tour_optional_reminder_mode" :value="tourOptionalReminderMode">

    {{-- Tour Master Toggle Card with Checkbox --}}
    <x-resellerhub::card
        class="p-4 sm:p-6 bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 transition-all">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center transition-all shrink-0"
                     :class="tourEnabled ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/80 shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">وضعیت فعال‌بودن تور
                            راه‌اندازی </h3>
                    </div>
                </div>
            </div>

            <label
                class="flex items-center justify-center sm:justify-start gap-2.5 px-4 py-2.5 rounded-xl border cursor-pointer select-none transition-all shrink-0 w-full sm:w-auto"
                :class="tourEnabled ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700'">
                <input type="checkbox"
                       name="onboarding_tour_enabled"
                       value="1"
                       x-model="tourEnabled"
                       @change="broadcastTourUpdate()"
                       class="w-5 h-5 text-indigo-600 rounded-lg border-gray-300 dark:border-gray-600 focus:ring-indigo-500 cursor-pointer">
                <span class="text-xs sm:text-sm font-black"
                      :class="tourEnabled ? 'text-indigo-950 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400'"
                      x-text="tourEnabled ? 'تور راه‌اندازی فعال است' : 'فعال‌سازی تور راه‌اندازی'"></span>
            </label>
        </div>
    </x-resellerhub::card>

    {{-- Interactive Step Manager & Sorter --}}
    <x-resellerhub::card
        class="p-6 sm:p-8 space-y-6 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        <div
            class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span
                    class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl shadow-xs border border-indigo-100/60 dark:border-indigo-900/40">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-base font-black text-gray-900 dark:text-white">مدیریت مراحل، ترتیب و الزامات
                            گام‌ها</h2>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button"
                        @click="chainTourStepsSequentially()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded-xl transition-all cursor-pointer border border-indigo-200/80 dark:border-indigo-800/80"
                        title="تنظیم خودکار تمام مراحل به صورت زنجیره‌ای (هر مرحله بعد از گام قبلی باز شود)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>زنجیره‌ای کردن گام‌ها</span>
                </button>

                <button type="button"
                        @click="unlockAllTourSteps()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition-all cursor-pointer border border-gray-200/60 dark:border-gray-700"
                        title="برداشتن وابستگی تمام مراحل جهت دسترسی آزاد و همزمان کاربر">
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    <span>آزادسازی همه گام‌ها</span>
                </button>

                <button type="button"
                        @click="resetTourStepsToDefault()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition-all cursor-pointer border border-gray-200/60 dark:border-gray-700"
                        title="بازنشانی تمامی مراحل به ۴ گام استاندارد سیستمی (احراز هویت، دامنه، وایت‌لیبل و پکیج)">
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>بازنشانی پیش‌فرض‌ها</span>
                </button>

                <button type="button"
                        @click="openAddCustomStepModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer border border-transparent">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن گام سفارشی جدید</span>
                </button>
            </div>
        </div>

        {{-- Steps List with Drag & Drop --}}
        <div class="space-y-3.5" x-ref="tourStepsContainer" x-init="initTourSortable()">
            <template x-for="(step, index) in tourSteps" :key="step.slug">
                <div
                    class="tour-step-card group relative bg-white dark:bg-gray-800 rounded-2xl border-2 transition-all duration-200 p-4 sm:p-5 shadow-xs"
                    :data-slug="step.slug"
                    :class="step.enabled ? (step.is_required ? 'border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500' : 'border-indigo-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500') : 'border-dashed border-gray-300 dark:border-gray-700 opacity-60 bg-gray-50/50 dark:bg-gray-900/60'">

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                        {{-- Left Side: Drag Handle, Step Number, Icon, Title & Details --}}
                        <div class="flex items-start sm:items-center gap-3.5 flex-1 min-w-0">

                            {{-- Modern Drag Handle --}}
                            <div
                                class="drag-handle shrink-0 flex items-center justify-center w-8 h-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200/90 dark:border-gray-700 text-gray-400 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 hover:border-indigo-300 dark:hover:border-indigo-600 cursor-grab active:cursor-grabbing transition-all select-none group/handle shadow-2xs"
                                title="برای تغییر ترتیب مرحله، بکشید و رها کنید (Drag to reorder)">
                                <svg class="w-4 h-4 transition-transform group-hover/handle:scale-110"
                                     viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="9" cy="5" r="1.75"/>
                                    <circle cx="15" cy="5" r="1.75"/>
                                    <circle cx="9" cy="12" r="1.75"/>
                                    <circle cx="15" cy="12" r="1.75"/>
                                    <circle cx="9" cy="19" r="1.75"/>
                                    <circle cx="15" cy="19" r="1.75"/>
                                </svg>
                            </div>

                            {{-- Step Number & Dynamic Icon Preview --}}
                            <div class="relative shrink-0">
                                <div
                                    class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-black text-xs shadow-xs transition-all"
                                    :class="step.enabled ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500'">
                                    <span class="w-5 h-5 flex items-center justify-center text-white"
                                          x-html="getStepIconSvg(step.icon)"></span>
                                    <span class="text-[9px] font-black opacity-90 mt-0.5"
                                          x-text="'#' + (index + 1)"></span>
                                </div>
                                <span
                                    class="absolute -bottom-1 -left-1 w-5 h-5 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-xs shadow-xs"
                                    :title="'نوع گام: ' + (step.is_core ? 'سیستمی' : 'سفارشی')">
                                    <template x-if="step.is_core">
                                        <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                                                stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path
                                                stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </template>
                                    <template x-if="!step.is_core">
                                        <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                                                stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    </template>
                                </span>
                            </div>

                            {{-- Step Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-black text-gray-900 dark:text-white"
                                        x-text="step.title"></h4>

                                    <span class="px-2 py-0.5 rounded-md text-xs font-black"
                                          :class="step.is_required ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60'"
                                          x-text="step.is_required ? 'اجباری' : 'اختیاری'"></span>

                                    <span
                                        class="px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700"
                                        x-text="step.badge || ('گام ' + (index + 1))"></span>

                                    <span x-show="!step.is_core"
                                          class="px-2 py-0.5 rounded-md text-xs font-black bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-900/60">
                                        گام اختصاصی
                                    </span>

                                    <template x-if="step.slug === 'configure_domain'">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-black transition-all"
                                            :class="allowCustomDomains ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'">
                                            <template x-if="allowCustomDomains">
                                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                </svg>
                                            </template>
                                            <template x-if="!allowCustomDomains">
                                                <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                                </svg>
                                            </template>
                                            <span
                                                x-text="allowCustomDomains ? 'دامنه اختصاصی: فعال' : 'فقط سابدامین ابری'"></span>
                                        </span>
                                    </template>
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1"
                                   x-text="step.description"></p>

                                {{-- Quick Domain Mode Selector for Step configure_domain --}}
                                <template x-if="step.slug === 'configure_domain'">
                                    <div
                                        class="mt-2.5 p-2.5 rounded-xl bg-gray-50/90 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2.5">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs shrink-0 font-bold">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="text-xs font-black text-gray-900 dark:text-white block">نوع دامنه در این مرحله:</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 block"
                                                      x-text="allowCustomDomains ? 'کاربران می‌توانند دامنه اختصاصی (مانند clinic.ir) را متصل کنند' : 'گزینه دامنه اختصاصی مخفی است و فقط سابدامین ابری نمایش داده می‌شود'"></span>
                                            </div>
                                        </div>

                                        <div
                                            class="inline-flex p-1 bg-gray-200/70 dark:bg-gray-800 rounded-xl text-xs gap-1 select-none">
                                            <button type="button"
                                                    @click.stop="allowCustomDomains = false"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
                                                    :class="!allowCustomDomains ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                                </svg>
                                                <span>فقط سابدامین ابری</span>
                                            </button>
                                            <button type="button"
                                                    @click.stop="allowCustomDomains = true"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
                                                    :class="allowCustomDomains ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                </svg>
                                                <span>+ دامنه اختصاصی</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Dependency display --}}
                                <div class="mt-1 flex items-center gap-1.5 text-xs">
                                    <template x-if="index === 0 && !step.depends_on">
                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                                stroke-linejoin="round"
                                                                                                d="M5 13l4 4L19 7"/></svg>
                                            <span>گام آغازین تور (شروع آزاد)</span>
                                        </span>
                                    </template>
                                    <template x-if="index > 0 || step.depends_on">
                                        <span class="flex items-center gap-1.5">
                                            <template x-if="!step.depends_on">
                                                <span class="text-gray-400 dark:text-gray-500 font-medium">دسترسی آزاد و مستقل (بدون پیش‌نیاز)</span>
                                            </template>
                                            <template x-if="step.depends_on">
                                                <span
                                                    class="text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                    </svg>
                                                    <span>پیش‌نیاز: </span>
                                                    <span class="font-bold"
                                                          x-text="getStepTitleBySlug(step.depends_on)"></span>
                                                </span>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side: Quick Switches & Actions --}}
                        <div class="flex items-center gap-3 self-end lg:self-center flex-wrap">

                            {{-- Position Selector Dropdown --}}
                            <div class="flex items-center gap-1.5 text-xs">
                                <span
                                    class="text-gray-400 dark:text-gray-400 text-xs hidden xl:inline">موقعیت:</span>
                                <select :value="index"
                                        x-effect="$el.value = index"
                                        @change="changeStepPosition(step.slug, $event.target.value)"
                                        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs px-2.5 py-1.5 focus:border-indigo-500 font-bold cursor-pointer transition-all shadow-2xs hover:border-indigo-400 dark:hover:border-indigo-500">
                                    <template x-for="(posStep, posIdx) in tourSteps" :key="'pos-' + posIdx">
                                        <option class="dark:bg-gray-800 dark:text-gray-200"
                                                :value="posIdx"
                                                :selected="posIdx === index"
                                                x-text="'ردیف ' + (posIdx + 1) + (posIdx === index ? ' (فعلی)' : '')"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dynamic Dependency & Auto-Reorder Selector --}}
                            <div class="flex items-center gap-1.5 text-xs">
                                <span
                                    class="text-gray-400 dark:text-gray-400 text-xs hidden xl:inline">پیش‌نیاز:</span>
                                <select x-model="step.depends_on"
                                        @change="setStepDependency(step.slug, step.depends_on)"
                                        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs px-2.5 py-1.5 focus:border-indigo-500 font-medium cursor-pointer transition-all shadow-2xs hover:border-indigo-400 dark:hover:border-indigo-500">
                                    <option class="dark:bg-gray-800 dark:text-gray-200" value="">بدون پیش‌نیاز (شروع
                                        آزاد)
                                    </option>
                                    <template x-for="candidate in getPrerequisiteCandidates(step.slug)"
                                              :key="candidate.slug">
                                        <option class="dark:bg-gray-800 dark:text-gray-200"
                                                :value="candidate.slug"
                                                x-text="'پس از گام ' + candidate.sort_order + ': ' + candidate.title"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Mandatory Toggle --}}
                            <div
                                class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900/70 px-3 py-1.5 rounded-xl border border-gray-200/80 dark:border-gray-700">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300">الزامی:</span>
                                <button type="button"
                                        @click="toggleStepRequired(step)"
                                        :disabled="step.is_core"
                                        :title="step.is_core ? 'مراحل اصلی سیستم همواره الزامی هستند' : ''"
                                        :class="step.is_required ? 'bg-rose-500 dark:bg-rose-600' : 'bg-gray-300 dark:bg-gray-700'"
                                        :class="step.is_core ? 'cursor-not-allowed opacity-80' : 'cursor-pointer'"
                                        class="relative inline-flex h-4 w-8 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200">
                                    <span :class="step.is_required ? 'translate-x-0' : '-translate-x-3.5'"
                                          class="inline-block h-3 w-3 transform rounded-full bg-white dark:bg-gray-300 shadow-xs transition duration-200"></span>
                                </button>
                            </div>

                            {{-- Enabled / Disabled Toggle --}}
                            <div
                                class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900/70 px-3 py-1.5 rounded-xl border border-gray-200/80 dark:border-gray-700">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300">فعال:</span>
                                <button type="button"
                                        @click="toggleStepEnabled(step)"
                                        :class="step.enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-700'"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                                    <span :class="step.enabled ? 'translate-x-0' : '-translate-x-4'"
                                          class="inline-block h-4 w-4 transform rounded-full bg-white dark:bg-gray-300 shadow-xs transition duration-200"></span>
                                </button>
                            </div>

                            {{-- Edit Details Button --}}
                            <button type="button"
                                    @click="openEditStepModal(index)"
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-xl transition-colors cursor-pointer"
                                    title="ویرایش متون و آیکون">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>

                            {{-- Delete Custom Step Button --}}
                            <button type="button"
                                    x-show="!step.is_core"
                                    @click="confirmDeleteStep(index)"
                                    class="p-2 text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition-colors cursor-pointer"
                                    title="حذف گام سفارشی">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </x-resellerhub::card>

    {{-- Card 3: Interactive Live Preview --}}
    <x-resellerhub::card
        class="p-4 sm:p-6 md:p-8 space-y-5 sm:space-y-6 bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/60">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <span
                    class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl shadow-xs border border-indigo-100/60 dark:border-indigo-900/40">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-black text-gray-900 dark:text-white">پیش‌نمایش زنده در داشبورد مدیر سامانه
                        </h2>
                </div>
            </div>
        </div>

        {{-- Simulated Stepper in Dashboard --}}
        <div
            class="bg-gray-50 dark:bg-gray-800/60 p-4 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-700 space-y-5 sm:space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span
                        class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">مراحل راه‌اندازی و فعال‌سازی سامانه
                            </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            <span x-text="activeEnabledSteps.length"></span> مرحله کلیدی فعال برای راه‌اندازی اولیه
                            سامانه
                        </p>
                    </div>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                    ۰٪ تکمیل شده
                </span>
            </div>

            {{-- Stepper Nodes Visual Track --}}
            <div class="relative flex items-center justify-between max-w-4xl mx-auto px-4 py-4">
                <div class="absolute top-9 start-6 end-6 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full z-0"></div>
                <template x-for="(s, idx) in activeEnabledSteps" :key="s.slug">
                    <div class="relative z-10 flex flex-col items-center group">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-xs shadow-sm"
                             :class="idx === 0 ? 'bg-indigo-600 text-white ring-4 ring-indigo-500/20' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 border border-gray-300 dark:border-gray-700'">
                            <span x-text="idx + 1"></span>
                        </div>
                        <div class="text-center mt-2 max-w-[110px]">
                            <span class="text-xs font-bold block truncate"
                                  :class="idx === 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'"
                                  x-text="s.title"></span>
                            <span class="text-xs text-gray-400 block mt-0.5"
                                  x-text="s.is_required ? 'اجباری' : 'اختیاری'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-resellerhub::card>

    {{-- Card 4: Optional Steps Reminder & Frequency Management --}}
    <x-resellerhub::card
        class="p-6 sm:p-8 space-y-6 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
        {{-- Header --}}
        <div
            class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span
                    class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-200/70 dark:border-indigo-800/50 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base font-black text-gray-900 dark:text-white">مدیریت یادآوری و نمایش مراحل اختیاری تور</h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        تعیین رفتار سیستم در صورتی که کاربر مراحل اجباری را گذرانده و دکمه «پر کردن بعداً» را برای مراحل
                        اختیاری انتخاب کند.
                    </p>
                </div>
            </div>
        </div>

        {{-- Master Toggle for Optional Reminder --}}
        <div class="p-5 rounded-2xl border transition-all"
             :class="tourOptionalReminderEnabled ? 'bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-200/80 dark:border-indigo-800/60' : 'bg-gray-50/70 dark:bg-gray-800/40 border-gray-200/80 dark:border-gray-700'">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5 transition-all"
                         :class="tourOptionalReminderEnabled ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25' : 'bg-gray-200 dark:bg-gray-700 text-gray-400'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">ارسال یادآور برای گام‌های
                                اختیاری تکمیل‌نشده</h3>
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold"
                                  :class="tourOptionalReminderEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'"
                                  x-text="tourOptionalReminderEnabled ? 'یادآوری خودکار فعال' : 'بدون یادآوری (غیرفعال)'"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            در صورت فعال بودن، اگر کاربر گزینه «پر کردن بعداً» را بزند، سامانه پس از بازه زمانی مقرر
                            دوباره مراحل اختیاری را به وی یادآوری خواهد کرد.
                        </p>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer shrink-0 select-none">
                    <input type="checkbox"
                           x-model="tourOptionalReminderEnabled"
                           class="sr-only peer">
                    <div
                        class="w-14 h-8 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-indigo-600 transition-colors duration-300 shadow-inner"></div>
                    <div
                        class="absolute right-1 top-1 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-300 peer-checked:-translate-x-6 flex items-center justify-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-indigo-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>
            </div>
        </div>

        {{-- Reminder Frequency / Interval Options (visible if enabled) --}}
        <div x-show="tourOptionalReminderEnabled" x-transition.duration.300ms class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>فاصله زمانی تکرار یادآوری (چند وقت یک‌بار نشان دهد؟):</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        مدت زمانی که پس از بستن موقت تور توسط کاربر، یادآور مجدداً ظاهر شود.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-2.5">
                {{-- Option 1: 24 Hours / Daily --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 1"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer"
                        :class="tourOptionalReminderIntervalDays === 1 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۲۴س</span>
                        <span x-show="tourOptionalReminderIntervalDays === 1"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">هر ۲۴ ساعت</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">یادآوری روزانه</span>
                    </div>
                </button>

                {{-- Option 2: 3 Days (Recommended) --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 3"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer relative"
                        :class="tourOptionalReminderIntervalDays === 3 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <span
                        class="absolute -top-2 start-3 px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-black rounded-md shadow-xs">پیشنهادی</span>
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۳ر</span>
                        <span x-show="tourOptionalReminderIntervalDays === 3"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">هر ۳ روز یک‌بار</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">تعادل مطلوب</span>
                    </div>
                </button>

                {{-- Option 3: 7 Days / Weekly --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 7"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer"
                        :class="tourOptionalReminderIntervalDays === 7 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۷ر</span>
                        <span x-show="tourOptionalReminderIntervalDays === 7"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">هر هفته (۷ روز)</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">یادآوری هفتگی</span>
                    </div>
                </button>

                {{-- Option 4: 14 Days --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 14"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer"
                        :class="tourOptionalReminderIntervalDays === 14 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۱۴ر</span>
                        <span x-show="tourOptionalReminderIntervalDays === 14"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">هر دو هفته</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">بازه طولانی‌تر</span>
                    </div>
                </button>

                {{-- Option 5: 30 Days / Monthly --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 30"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer"
                        :class="tourOptionalReminderIntervalDays === 30 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۳۰ر</span>
                        <span x-show="tourOptionalReminderIntervalDays === 30"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">هر ماه (۳۰ روز)</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">ماهانه یک‌بار</span>
                    </div>
                </button>

                {{-- Option 6: Every Session / Login (0) --}}
                <button type="button"
                        @click="tourOptionalReminderIntervalDays = 0"
                        class="p-3.5 rounded-2xl border text-right transition-all flex flex-col justify-between gap-2.5 cursor-pointer"
                        :class="tourOptionalReminderIntervalDays === 0 ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                    <div class="flex items-center justify-between w-full">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">ورود</span>
                        <span x-show="tourOptionalReminderIntervalDays === 0"
                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    </div>
                    <div>
                        <span class="text-xs font-black block">در هر بار ورود</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">در هر سشن جدید</span>
                    </div>
                </button>
            </div>
        </div>

        {{-- Presentation Mode: Pill vs Auto-Expanded Card --}}
        <div x-show="tourOptionalReminderEnabled" x-transition.duration.300ms
             class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-800">
            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span>حالت نمایش یادآور در پیشخوان (Reminder Display Mode):</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button"
                        @click="tourOptionalReminderMode = 'pill'"
                        class="p-4 rounded-2xl border text-right transition-all flex items-start gap-3.5 cursor-pointer"
                        :class="tourOptionalReminderMode === 'pill' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                          :class="tourOptionalReminderMode === 'pill' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-gray-900 dark:text-white">نوار جمع‌شده و مینیمال (Pill / Ribbon)</span>
                            <span x-show="tourOptionalReminderMode === 'pill'"
                                  class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            یک نوار باریک، شکیل و آرامش‌بخش در بالای داشبورد نمایش می‌یابد که مزاحم کار کاربر نمی‌شود و
                            با یک کلیک باز می‌گردد.
                        </p>
                    </div>
                </button>

                <button type="button"
                        @click="tourOptionalReminderMode = 'card'"
                        class="p-4 rounded-2xl border text-right transition-all flex items-start gap-3.5 cursor-pointer"
                        :class="tourOptionalReminderMode === 'card' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                          :class="tourOptionalReminderMode === 'card' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-gray-900 dark:text-white">باز شدن خودکار کارد مراحل (Auto-Expanded Card)</span>
                            <span x-show="tourOptionalReminderMode === 'card'"
                                  class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            پس از فرارسیدن موعد یادآوری، کارد کامل مراحل در داشبورد باز می‌شود تا توجه کاربر به تکمیل
                            مراحل باقیمانده جلب شود.
                        </p>
                    </div>
                </button>
            </div>
        </div>

        {{-- Helper Information Tip --}}
        <div
            class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 flex items-start gap-3">
            <span
                class="p-1 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed">
                <span class="font-bold">راهنمای هوشمند عملکرد تور:</span>
                هنگامی که تمام مراحل اجباری راه‌اندازی توسط کاربر انجام شود و فقط مراحل اختیاری باقی مانده باشد، دکمه
                شکیل <strong>«پر کردن بعداً و ورود به پیشخوان»</strong> در بالای تور نمایش داده می‌شود. با کلیک کاربر
                روی آن، تور فوراً بسته شده و پیشخوان کامل CRM باز می‌گردد، و طبق بازه زمانی تعریف‌شده در این بخش، در
                آینده مجدداً یادآوری خواهد شد.
            </div>
        </div>
    </x-resellerhub::card>

    {{-- Edit Step Modal --}}
    <template x-teleport="body">
        <div x-show="editStepModalOpen"
             x-cloak
             class="fixed inset-0 z-[99999] overflow-y-auto"
             role="dialog"
             aria-modal="true"
             dir="rtl">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-6">
                {{-- Backdrop (Behind Modal) --}}
                <div x-show="editStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-xs transition-opacity"
                     @click="editStepModalOpen = false"
                     aria-hidden="true"></div>

                {{-- Modal Card (In Front with relative z-10) --}}
                <div x-show="editStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative z-10 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all border border-gray-100 dark:border-gray-700/80 p-6 sm:p-7 space-y-5 my-8">

                    <div
                        class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-gray-700/80">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </span>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">ویرایش مشخصات مرحله</h3>
                        </div>
                        <button type="button" @click="editStepModalOpen = false"
                                class="p-1.5 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <x-resellerhub::label>عنوان گام: <span class="text-rose-500">*</span></x-resellerhub::label>
                            <input type="text" x-model="editingStep.title"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:border-indigo-500">
                        </div>

                        <div class="space-y-1.5">
                            <x-resellerhub::label>توضیحات مختصر گام:</x-resellerhub::label>
                            <textarea x-model="editingStep.description" rows="2"
                                      class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 text-xs text-gray-900 dark:text-white focus:border-indigo-500"></textarea>
                        </div>

                        {{-- Special Domain Mode Selector inside Edit Modal --}}
                        <template x-if="editingStep && editingStep.slug === 'configure_domain'">
                            <div
                                class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                        نحوه اتصال دامنه در این مرحله
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black"
                                          :class="allowCustomDomains ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                          x-text="allowCustomDomains ? 'دامنه اختصاصی فعال' : 'فقط سابدامین'"></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <button type="button"
                                            @click="allowCustomDomains = false"
                                            class="p-3 rounded-xl border-2 text-xs font-bold transition-all text-center flex flex-col items-center gap-1.5 cursor-pointer"
                                            :class="!allowCustomDomains ? 'border-indigo-600 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                        </svg>
                                        <span class="font-black">فقط سابدامین ابری</span>
                                        <span class="text-xs opacity-75">دامنه اختصاصی مخفی می‌شود</span>
                                    </button>
                                    <button type="button"
                                            @click="allowCustomDomains = true"
                                            class="p-3 rounded-xl border-2 text-xs font-bold transition-all text-center flex flex-col items-center gap-1.5 cursor-pointer"
                                            :class="allowCustomDomains ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                        <span class="font-black">+ دامنه اختصاصی</span>
                                        <span class="text-xs opacity-85">کاربر می‌تواند دامنه مستقل متصل کند</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="space-y-1.5">
                            <x-resellerhub::label>برچسب / نشان (Badge):</x-resellerhub::label>
                            <input type="text" x-model="editingStep.badge" placeholder="مثال: گام اول"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold text-gray-900 dark:text-white">
                        </div>

                        {{-- Visual Icon Picker --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <x-resellerhub::label>انتخاب تصویری آیکون مرحله:</x-resellerhub::label>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-400">آیکون فعال:</span>
                                    <span
                                        class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow-xs"
                                        x-html="getStepIconSvg(editingStep.icon)"></span>
                                </div>
                            </div>
                            <div
                                class="grid grid-cols-6 sm:grid-cols-9 gap-2 p-2.5 bg-gray-50 dark:bg-gray-900/60 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                                <template x-for="item in iconCatalog" :key="item.id">
                                    <button type="button"
                                            @click="editingStep.icon = item.id"
                                            :title="item.label"
                                            class="p-2 rounded-xl flex items-center justify-center transition-all cursor-pointer"
                                            :class="editingStep.icon === item.id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 ring-2 ring-indigo-400 scale-105' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700/60'">
                                        <span class="w-5 h-5 flex items-center justify-center" x-html="item.svg"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Step Dependency / Prerequisite --}}
                        <div class="space-y-1.5 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <x-resellerhub::label>پیش‌نیاز تکمیل این مرحله (مرتب‌سازی خودکار):
                                </x-resellerhub::label>
                                <span
                                    class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">تغییر جایگاه هوشمند</span>
                            </div>
                            <select x-model="editingStep.depends_on"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold text-gray-900 dark:text-white">
                                <option value="">بدون پیش‌نیاز (شروع آزاد)</option>
                                <template x-for="candidate in getPrerequisiteCandidates(editingStep.slug)"
                                          :key="candidate.slug">
                                    <option :value="candidate.slug"
                                            x-text="'پس از گام ' + candidate.sort_order + ': ' + candidate.title"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                با تعیین پیش‌نیاز، این گام به‌طور خودکار بلافاصله پس از مرحله انتخابی در لیست قرار خواهد
                                گرفت.
                            </p>
                        </div>

                        {{-- Custom Step Action Config --}}
                        <div x-show="!editingStep.is_core"
                             class="space-y-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="space-y-1.5">
                                <x-resellerhub::label>نوع اقدام (Action):</x-resellerhub::label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button"
                                            @click="editingStep.action_type = 'internal'"
                                            class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center gap-1.5"
                                            :class="editingStep.action_type === 'internal' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span class="text-xs font-black">صفحه داخلی CRM</span>
                                    </button>
                                    <button type="button"
                                            @click="editingStep.action_type = 'external'"
                                            class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center gap-1.5"
                                            :class="editingStep.action_type === 'external' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                                        <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        <span class="text-xs font-black">لینک بیرونی</span>
                                    </button>
                                    <button type="button"
                                            @click="editingStep.action_type = 'manual'"
                                            class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center gap-1.5"
                                            :class="editingStep.action_type === 'manual' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-xs font-black">چک‌ لیست دستی</span>
                                    </button>
                                    <button type="button"
                                            @click="editingStep.action_type = 'custom_fields'"
                                            class="p-2.5 rounded-xl border text-center transition-all cursor-pointer flex flex-col items-center gap-1.5"
                                            :class="editingStep.action_type === 'custom_fields' ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400'">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <span class="text-xs font-black">فیلدهای سفارشی</span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="editingStep.action_type === 'internal'" class="space-y-1.5">
                                <x-resellerhub::label>مسیر صفحه در CRM:</x-resellerhub::label>
                                <select x-model="editingStep.internal_route"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white">
                                    <template x-for="opt in internalRouteOptions" :key="opt.route">
                                        <option :value="opt.route" x-text="opt.title"></option>
                                    </template>
                                </select>
                                <div x-show="editingStep.internal_route === 'custom'" class="pt-1">
                                    <input type="text" x-model="editingStep.custom_internal_route"
                                           placeholder="مثال: /user/tickets/create"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-mono text-left text-gray-900 dark:text-white">
                                </div>
                            </div>

                            <div x-show="editingStep.action_type === 'external'" class="space-y-1.5">
                                <x-resellerhub::label>آدرس اینترنتی (URL):</x-resellerhub::label>
                                <input type="url" x-model="editingStep.action_url" placeholder="https://..."
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs text-left font-mono text-gray-900 dark:text-white">
                            </div>

                            <div x-show="editingStep.action_type === 'custom_fields'" class="space-y-3">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                                    <div class="space-y-1.5">
                                        <x-resellerhub::label>دسته‌بندی موضوعی:</x-resellerhub::label>
                                        <select x-model="editingStep.field_group"
                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white">
                                            <option value="contact">اطلاعات تماس، فکس و نشانی اداری</option>
                                            <option value="identity">مشخصات حقوقی و هویتی</option>
                                            <option value="business">اهداف و اطلاعات تجاری</option>
                                            <option value="system">پارامترها و مشخصات سیستم</option>
                                            <option value="general">اطلاعات تکمیلی و متفرقه</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <x-resellerhub::label>اقامتگاه دائمی فیلدها:</x-resellerhub::label>
                                        <select x-model="editingStep.target_scope"
                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white">
                                            <option value="user">پروفایل کاربری مدیر (/user/profile)</option>
                                            <option value="tenant">تنظیمات کسب‌وکار و شرکت (/user/settings)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <span class="text-xs font-black text-gray-800 dark:text-gray-200">الگوهای آماده و فیلدهای قابل انتخاب:</span>
                                        <button type="button" @click="addCustomFieldToEditStep()"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-all cursor-pointer shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span>+ فیلد سفارشی دلخواه</span>
                                        </button>
                                    </div>

                                    {{-- Categorized Preset Tabs / Buttons --}}
                                    <div class="p-3 rounded-2xl bg-gray-100/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 space-y-2.5">
                                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                                            <template x-for="cat in fieldPresetsCatalog" :key="cat.category">
                                                <button type="button"
                                                        @click="editActivePresetTab = cat.category"
                                                        class="px-2.5 py-1 rounded-lg font-bold transition-all whitespace-nowrap cursor-pointer"
                                                        :class="editActivePresetTab === cat.category ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700'"
                                                        x-text="cat.categoryLabel"></button>
                                            </template>
                                            <template x-if="availableSystemFields && availableSystemFields.length > 0">
                                                <button type="button"
                                                        @click="editActivePresetTab = 'system_database'"
                                                        class="px-2.5 py-1 rounded-lg font-bold transition-all whitespace-nowrap cursor-pointer"
                                                        :class="editActivePresetTab === 'system_database' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800'">
                                                    فیلدهای سیستم (<span x-text="availableSystemFields.length"></span>)
                                                </button>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                            <template x-for="cat in fieldPresetsCatalog" :key="cat.category">
                                                <div x-show="editActivePresetTab === cat.category" class="flex items-center gap-1.5 flex-wrap w-full">
                                                    <template x-for="fld in cat.fields" :key="fld.name">
                                                        <button type="button"
                                                                @click="addCustomFieldToEditStep(fld)"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-700 dark:text-gray-200 hover:text-indigo-600 border border-gray-200/80 dark:border-gray-700 transition-all cursor-pointer shadow-2xs">
                                                            <span class="text-indigo-500 font-black">+</span>
                                                            <span x-text="fld.label"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="availableSystemFields && availableSystemFields.length > 0">
                                                <div x-show="editActivePresetTab === 'system_database'" class="flex items-center gap-1.5 flex-wrap w-full">
                                                    <template x-for="fld in availableSystemFields" :key="fld.field_name">
                                                        <button type="button"
                                                                @click="addCustomFieldToEditStep(fld)"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800 transition-all cursor-pointer shadow-2xs">
                                                            <span class="text-purple-600 font-black">+</span>
                                                            <span x-text="fld.label"></span>
                                                            <span class="text-[10px] opacity-75 font-mono" x-text="'(' + fld.field_name + ')'"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <template x-if="!editingStep.custom_fields || editingStep.custom_fields.length === 0">
                                        <div class="p-5 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-900/40 space-y-1">
                                            <p class="font-bold">هنوز فیلدی به این مرحله اضافه نشده است.</p>
                                            <p>می‌توانید از الگوهای آماده بالا فیلد انتخاب کنید یا دکمه «+ فیلد سفارشی دلخواه» را بزنید.</p>
                                        </div>
                                    </template>

                                    <div class="space-y-2.5">
                                        <template x-for="(fld, fIdx) in editingStep.custom_fields" :key="fIdx">
                                            <div class="p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 shadow-2xs space-y-2.5">
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                                    <div class="sm:col-span-4">
                                                        <input type="text" x-model="fld.label" placeholder="برچسب فارسی فیلد (مثال: شماره فکس)"
                                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1.5 text-xs font-bold text-gray-900 dark:text-white">
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <input type="text" x-model="fld.name" placeholder="کلید انگلیسی (fax)"
                                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white">
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <select x-model="fld.type"
                                                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-2 py-1.5 text-xs font-bold text-gray-900 dark:text-white">
                                                            <option value="text">متن تک‌خطی</option>
                                                            <option value="number">عددی</option>
                                                            <option value="currency">مبلغ مالی (تومان)</option>
                                                            <option value="tel">تلفن ثابت / فکس</option>
                                                            <option value="mobile">شماره همراه</option>
                                                            <option value="email">آدرس ایمیل</option>
                                                            <option value="date">تاریخ</option>
                                                            <option value="time">ساعت / زمان</option>
                                                            <option value="textarea">متن چندخطی</option>
                                                            <option value="select">منوی کشویی گزینه‌ای</option>
                                                            <option value="checkbox">تیک‌باکس (بلی/خیر)</option>
                                                            <option value="url">وب‌سایت / لینک اینترنتی</option>
                                                        </select>
                                                    </div>
                                                    <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-bold text-gray-700 dark:text-gray-300">
                                                            <input type="checkbox" x-model="fld.is_required"
                                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                            <span>اجباری</span>
                                                        </label>
                                                        <button type="button" @click="removeCustomFieldFromEditStep(fIdx)"
                                                                class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer shrink-0"
                                                                title="حذف این فیلد">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div x-show="fld.type === 'select'" class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 shrink-0">گزینه‌های منو (با کاما):</span>
                                                        <input type="text" x-model="fld.options_str"
                                                               @input="fld.options = fld.options_str ? fld.options_str.split(',').map(s => s.trim()).filter(Boolean) : []"
                                                               placeholder="مثال: مطب مرکزی, شعبه شرق, کلینیک تخصصی"
                                                               class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1 text-xs text-gray-900 dark:text-white">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/80 flex justify-end gap-3">
                        <button type="button" @click="editStepModalOpen = false"
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            انصراف
                        </button>
                        <button type="button" @click="saveEditingStep()"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                            اعمال تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Add Custom Step Modal --}}
    <template x-teleport="body">
        <div x-show="addCustomStepModalOpen"
             x-cloak
             class="fixed inset-0 z-[99999] overflow-y-auto"
             role="dialog"
             aria-modal="true"
             dir="rtl">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-6">
                {{-- Backdrop (Behind Modal) --}}
                <div x-show="addCustomStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-950/70 dark:bg-black/85 backdrop-blur-xs transition-opacity"
                     @click="addCustomStepModalOpen = false"
                     aria-hidden="true"></div>

                {{-- Modal Card (In Front with relative z-10) --}}
                <div x-show="addCustomStepModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative z-10 w-full max-w-3xl bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all border border-gray-200/80 dark:border-gray-700 p-5 sm:p-7 space-y-5 my-6 max-h-[92vh] flex flex-col">

                    {{-- Modal Header --}}
                    <div
                        class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/80 shrink-0">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">طراح
                                        هوشمند گام سفارشی جدید</h3>
                                    <span
                                        class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800">Custom Step Builder</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">یک مرحله اختصاصی همراه با
                                    آیکون، مقصد اقدام، الزامات و پیش‌نیاز تعریف کنید یا از الگوهای آماده استفاده
                                    نمایید.</p>
                            </div>
                        </div>
                        <button type="button" @click="addCustomStepModalOpen = false"
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body (Scrollable, Full Width) --}}
                    <div class="space-y-4.5 overflow-y-auto px-1 -mx-1 text-right flex-1">
                        {{-- Presets Bar --}}
                        <div
                            class="p-4 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-2xl border border-indigo-200/70 dark:border-indigo-800/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    الگوهای آماده و سناریوهای رایج (کلیک برای تکمیل خودکار):
                                </span>
                                <span
                                    class="text-xs text-indigo-600 dark:text-indigo-400 font-bold bg-white/80 dark:bg-gray-800/80 px-2.5 py-0.5 rounded-lg border border-indigo-100 dark:border-indigo-900/60">۸ سناریو پیشنهادی</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="preset in customStepPresets" :key="preset.id || preset.key">
                                    <button type="button"
                                            @click="applyCustomStepPreset(preset)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200/90 dark:border-gray-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:border-indigo-400 dark:hover:text-indigo-300 shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                                        <span
                                            class="w-4 h-4 flex items-center justify-center text-indigo-600 dark:text-indigo-400"
                                            x-html="getStepIconSvg(preset.icon)"></span>
                                        <span x-text="preset.title"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Section 1: Step Identity --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-gray-200/60 dark:border-gray-800">
                                <span
                                    class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۱</span>
                                <h4 class="text-xs font-black text-gray-900 dark:text-white">مشخصات و عنوان مرحله</h4>
                            </div>

                            {{-- Title Input --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-black text-gray-800 dark:text-gray-200">
                                        عنوان مرحله: <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-xs text-gray-400 font-mono"
                                          x-text="(newStep.title || '').length + '/80'"></span>
                                </div>
                                <input type="text"
                                       x-model="newStep.title"
                                       @input="newStepError = ''"
                                       maxlength="80"
                                       placeholder="مثال: دعوت اعضای تیم یا ثبت اولین مشتری"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                       :class="{ 'border-rose-500 ring-2 ring-rose-500/20': newStepError }">
                                <p x-show="newStepError" x-text="newStepError"
                                   class="text-xs text-rose-500 font-bold mt-1.5"></p>
                            </div>

                            {{-- Badge Input --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-black text-gray-800 dark:text-gray-200">
                                        برچسب یا نشان مرحله (Badge):
                                    </label>
                                    <span class="text-xs text-gray-400">اختیاری</span>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                                    <input type="text"
                                           x-model="newStep.badge"
                                           placeholder="مثال: ویژه، مهم، پیشنهاد"
                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span class="text-xs text-gray-400 font-medium">پیشنهاد:</span>
                                        <button type="button" @click="newStep.badge = 'ویژه'"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer">
                                            ویژه
                                        </button>
                                        <button type="button" @click="newStep.badge = 'ضروری'"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer">
                                            ضروری
                                        </button>
                                        <button type="button" @click="newStep.badge = 'پیشنهادی'"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer">
                                            پیشنهادی
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Description Input --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-800 dark:text-gray-200 block">
                                    توضیحات راهنما برای کاربر (اختیاری):
                                </label>
                                <textarea x-model="newStep.description"
                                          rows="2"
                                          placeholder="توضیح دهید که کاربر در این گام چه کاری باید انجام دهد..."
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                            </div>

                            {{-- Modern Is Required Toggle Switch --}}
                            <div
                                class="flex items-center justify-between p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700">
                                <div>
                                    <span class="text-xs font-black text-gray-800 dark:text-gray-200 block">گام اجباری برای تکمیل راه‌اندازی</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">تکمیل تور راه‌اندازی منوط به انجام این مرحله خواهد بود.</span>
                                </div>
                                <button type="button"
                                        @click="newStep.is_required = !newStep.is_required"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                                        :class="newStep.is_required ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-700'">
                                    <span
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                        :class="newStep.is_required ? '-translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Section 2: Action Type & Destination --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-3.5">
                            <div
                                class="flex items-center gap-2 pb-1.5 border-b border-gray-200/60 dark:border-gray-800">
                                <span
                                    class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۲</span>
                                <h4 class="text-xs font-black text-gray-900 dark:text-white">نوع اقدام و لینک مقصد</h4>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                                <button type="button"
                                        @click="newStep.action_type = 'internal'"
                                        class="p-3.5 rounded-xl border text-right transition-all cursor-pointer relative flex flex-col justify-between gap-2"
                                        :class="newStep.action_type === 'internal' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <div class="flex items-center justify-between w-full">
                                        <span
                                            class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </span>
                                        <span x-show="newStep.action_type === 'internal'"
                                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black block">صفحه داخلی CRM</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">انتقال کاربر به بخش‌های درونی پنل</span>
                                    </div>
                                </button>

                                <button type="button"
                                        @click="newStep.action_type = 'external'"
                                        class="p-3.5 rounded-xl border text-right transition-all cursor-pointer relative flex flex-col justify-between gap-2"
                                        :class="newStep.action_type === 'external' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <div class="flex items-center justify-between w-full">
                                        <span
                                            class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </span>
                                        <span x-show="newStep.action_type === 'external'"
                                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black block">لینک بیرونی</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">پرتال، وبلاگ، کانال‌ها یا وب‌سایت</span>
                                    </div>
                                </button>

                                <button type="button"
                                        @click="newStep.action_type = 'manual'"
                                        class="p-3.5 rounded-xl border text-right transition-all cursor-pointer relative flex flex-col justify-between gap-2"
                                        :class="newStep.action_type === 'manual' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <div class="flex items-center justify-between w-full">
                                        <span
                                            class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                        <span x-show="newStep.action_type === 'manual'"
                                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black block">چک‌لیست دستی</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">تأیید مستقیم توسط خود کاربر</span>
                                    </div>
                                </button>

                                <button type="button"
                                        @click="newStep.action_type = 'custom_fields'"
                                        class="p-3.5 rounded-xl border text-right transition-all cursor-pointer relative flex flex-col justify-between gap-2"
                                        :class="newStep.action_type === 'custom_fields' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <div class="flex items-center justify-between w-full">
                                        <span
                                            class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                        </span>
                                        <span x-show="newStep.action_type === 'custom_fields'"
                                              class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black block">فیلدهای سفارشی</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">فرم عددی، فکس، کد پستی یا فیلد دلخواه</span>
                                    </div>
                                </button>
                            </div>

                            {{-- Target Input Area --}}
                            <div
                                class="p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700">
                                {{-- If Internal --}}
                                <div x-show="newStep.action_type === 'internal'" class="space-y-2">
                                    <x-resellerhub::label>انتخاب صفحه مقصد در CRM:</x-resellerhub::label>
                                    <select x-model="newStep.internal_route"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-2.5 text-xs font-bold text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                        <template x-for="opt in internalRouteOptions" :key="opt.route">
                                            <option :value="opt.route" x-text="opt.title"></option>
                                        </template>
                                    </select>
                                    <div x-show="newStep.internal_route === 'custom'" class="pt-1">
                                        <input type="text"
                                               x-model="newStep.custom_internal_route"
                                               placeholder="مثال: /user/tickets/create یا /user/contacts"
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3.5 py-2 text-xs font-mono text-left text-gray-900 dark:text-white">
                                    </div>
                                </div>

                                {{-- If External --}}
                                <div x-show="newStep.action_type === 'external'" class="space-y-1.5">
                                    <x-resellerhub::label>آدرس اینترنتی مقصد (URL):</x-resellerhub::label>
                                    <input type="url"
                                           x-model="newStep.action_url"
                                           placeholder="https://example.com/guide یا https://t.me/..."
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3.5 py-2.5 text-xs text-left font-mono text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                </div>

                                {{-- If Manual --}}
                                <div x-show="newStep.action_type === 'manual'"
                                     class="flex items-center gap-2.5 text-xs text-emerald-800 dark:text-emerald-300 py-1">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>این مرحله نیاز به URL ندارد؛ در پنل کاربر دکمه‌ای برای ثبت «تأیید و انجام شد» تعبیه می‌شود.</span>
                                </div>

                                {{-- If Custom Fields --}}
                                <div x-show="newStep.action_type === 'custom_fields'" class="space-y-4">
                                    {{-- Visual Category & Scope Pickers --}}
                                    <div class="space-y-3.5 pb-3 border-b border-gray-100 dark:border-gray-700">
                                        {{-- 1. Category Selector --}}
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <x-resellerhub::label>دسته‌بندی موضوعی فیلدها در پروفایل:
                                                </x-resellerhub::label>
                                                <span class="text-[11px] text-gray-400 font-bold"
                                                      x-text="newStep.field_group === 'contact' ? 'اطلاعات تماس، فکس و نشانی اداری' : (newStep.field_group === 'identity' ? 'مشخصات حقوقی و هویتی' : (newStep.field_group === 'business' ? 'اهداف و اطلاعات تجاری' : (newStep.field_group === 'system' ? 'پارامترها و مشخصات سیستم' : 'اطلاعات تکمیلی و متفرقه')))"></span>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5">
                                                <button type="button" @click="newStep.field_group = 'contact'"
                                                        :class="newStep.field_group === 'contact' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 text-center transition-all cursor-pointer">
                                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    <span class="text-[11px] leading-tight block">تماس و نشانی</span>
                                                </button>

                                                <button type="button" @click="newStep.field_group = 'identity'"
                                                        :class="newStep.field_group === 'identity' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 text-center transition-all cursor-pointer">
                                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                                    </svg>
                                                    <span class="text-[11px] leading-tight block">هویتی و حقوقی</span>
                                                </button>

                                                <button type="button" @click="newStep.field_group = 'business'"
                                                        :class="newStep.field_group === 'business' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 text-center transition-all cursor-pointer">
                                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                    </svg>
                                                    <span class="text-[11px] leading-tight block">اهداف و تجاری</span>
                                                </button>

                                                <button type="button" @click="newStep.field_group = 'system'"
                                                        :class="newStep.field_group === 'system' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 text-center transition-all cursor-pointer">
                                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span class="text-[11px] leading-tight block">سیستم و فنی</span>
                                                </button>

                                                <button type="button" @click="newStep.field_group = 'general'"
                                                        :class="newStep.field_group === 'general' ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20 font-bold' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2 rounded-xl border flex flex-col items-center justify-center gap-1 text-center transition-all cursor-pointer col-span-2 sm:col-span-1">
                                                    <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                                    </svg>
                                                    <span class="text-[11px] leading-tight block">تکمیلی و عمومی</span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 2. Target Scope Selector --}}
                                        <div class="space-y-1.5">
                                            <x-resellerhub::label>اقامتگاه دائمی فیلدها پس از اتمام تور:
                                            </x-resellerhub::label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <button type="button" @click="newStep.target_scope = 'user'"
                                                        :class="newStep.target_scope === 'user' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-2xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2.5 rounded-xl border flex items-center gap-2.5 text-right transition-all cursor-pointer">
                                                    <span
                                                        class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <span class="text-xs font-black block truncate">پروفایل کاربری مدیر</span>
                                                        <span
                                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-mono dir-ltr block truncate">/user/profile</span>
                                                    </div>
                                                </button>

                                                <button type="button" @click="newStep.target_scope = 'tenant'"
                                                        :class="newStep.target_scope === 'tenant' ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-2xs' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
                                                        class="p-2.5 rounded-xl border flex items-center gap-2.5 text-right transition-all cursor-pointer">
                                                    <span
                                                        class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                        </svg>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <span class="text-xs font-black block truncate">تنظیمات مطب و کلینیک</span>
                                                        <span
                                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-mono dir-ltr block truncate">/user/settings</span>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <span class="text-xs font-black text-gray-800 dark:text-gray-200">الگوهای آماده و فیلدهای قابل انتخاب:</span>
                                            <button type="button" @click="addCustomFieldToNewStep()"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-all cursor-pointer shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>+ فیلد سفارشی دلخواه</span>
                                            </button>
                                        </div>

                                        {{-- Categorized Preset Tabs / Buttons --}}
                                        <div class="p-3 rounded-2xl bg-gray-100/70 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 space-y-2.5">
                                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                                                <template x-for="cat in fieldPresetsCatalog" :key="cat.category">
                                                    <button type="button"
                                                            @click="activePresetTab = cat.category"
                                                            class="px-2.5 py-1 rounded-lg font-bold transition-all whitespace-nowrap cursor-pointer"
                                                            :class="activePresetTab === cat.category ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700'"
                                                            x-text="cat.categoryLabel"></button>
                                                </template>
                                                <template x-if="availableSystemFields && availableSystemFields.length > 0">
                                                    <button type="button"
                                                            @click="activePresetTab = 'system_database'"
                                                            class="px-2.5 py-1 rounded-lg font-bold transition-all whitespace-nowrap cursor-pointer"
                                                            :class="activePresetTab === 'system_database' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800'">
                                                        فیلدهای سیستم (<span x-text="availableSystemFields.length"></span>)
                                                    </button>
                                                </template>
                                            </div>

                                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                                <template x-for="cat in fieldPresetsCatalog" :key="cat.category">
                                                    <div x-show="activePresetTab === cat.category" class="flex items-center gap-1.5 flex-wrap w-full">
                                                        <template x-for="fld in cat.fields" :key="fld.name">
                                                            <button type="button"
                                                                    @click="addCustomFieldToNewStep(fld)"
                                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-gray-700 dark:text-gray-200 hover:text-indigo-600 border border-gray-200/80 dark:border-gray-700 transition-all cursor-pointer shadow-2xs">
                                                                <span class="text-indigo-500 font-black">+</span>
                                                                <span x-text="fld.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="availableSystemFields && availableSystemFields.length > 0">
                                                    <div x-show="activePresetTab === 'system_database'" class="flex items-center gap-1.5 flex-wrap w-full">
                                                        <template x-for="fld in availableSystemFields" :key="fld.field_name">
                                                            <button type="button"
                                                                    @click="addCustomFieldToNewStep(fld)"
                                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800 transition-all cursor-pointer shadow-2xs">
                                                                <span class="text-purple-600 font-black">+</span>
                                                                <span x-text="fld.label"></span>
                                                                <span class="text-[10px] opacity-75 font-mono" x-text="'(' + fld.field_name + ')'"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <template x-if="!newStep.custom_fields || newStep.custom_fields.length === 0">
                                            <div class="p-5 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-900/40 space-y-1">
                                                <p class="font-bold">هنوز فیلدی به این مرحله اضافه نشده است.</p>
                                                <p>می‌توانید از الگوهای آماده بالا فیلد انتخاب کنید یا دکمه «+ فیلد سفارشی دلخواه» را بزنید.</p>
                                            </div>
                                        </template>

                                        <div class="space-y-2.5">
                                            <template x-for="(fld, fIdx) in newStep.custom_fields" :key="fIdx">
                                                <div class="p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 shadow-2xs space-y-2.5">
                                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                                        <div class="sm:col-span-4">
                                                            <input type="text" x-model="fld.label" placeholder="برچسب فارسی فیلد (مثال: شماره پروانه)"
                                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1.5 text-xs font-bold text-gray-900 dark:text-white">
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <input type="text" x-model="fld.name" placeholder="کلید انگلیسی (license_no)"
                                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1.5 text-xs font-mono dir-ltr text-left text-gray-900 dark:text-white">
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <select x-model="fld.type"
                                                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-2 py-1.5 text-xs font-bold text-gray-900 dark:text-white">
                                                                <option value="text">متن تک‌خطی</option>
                                                                <option value="number">عددی</option>
                                                                <option value="currency">مبلغ مالی (تومان)</option>
                                                                <option value="tel">تلفن ثابت / فکس</option>
                                                                <option value="mobile">شماره همراه</option>
                                                                <option value="email">آدرس ایمیل</option>
                                                                <option value="date">تاریخ</option>
                                                                <option value="time">ساعت / زمان</option>
                                                                <option value="textarea">متن چندخطی</option>
                                                                <option value="select">منوی کشویی گزینه‌ای</option>
                                                                <option value="checkbox">تیک‌باکس (بلی/خیر)</option>
                                                                <option value="url">وب‌سایت / لینک اینترنتی</option>
                                                            </select>
                                                        </div>
                                                        <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-bold text-gray-700 dark:text-gray-300">
                                                                <input type="checkbox" x-model="fld.is_required"
                                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                <span>اجباری</span>
                                                            </label>
                                                            <button type="button" @click="removeCustomFieldFromNewStep(fIdx)"
                                                                    class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer shrink-0"
                                                                    title="حذف این فیلد">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div x-show="fld.type === 'select'" class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 shrink-0">گزینه‌های منو (با کاما):</span>
                                                            <input type="text" x-model="fld.options_str"
                                                                   @input="fld.options = fld.options_str ? fld.options_str.split(',').map(s => s.trim()).filter(Boolean) : []"
                                                                   placeholder="مثال: مطب مرکزی, شعبه شرق, کلینیک تخصصی"
                                                                   class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 px-3 py-1 text-xs text-gray-900 dark:text-white">
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Visual Icon Palette --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                            <div
                                class="flex items-center justify-between pb-1.5 border-b border-gray-200/60 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-black">۳</span>
                                    <h4 class="text-xs font-black text-gray-900 dark:text-white">انتخاب تصویری آیکون
                                        گام</h4>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    <span class="text-xs text-gray-400">آیکون فعال:</span>
                                    <span
                                        class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center shadow-xs"
                                        x-html="getStepIconSvg(newStep.icon)"></span>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-6 sm:grid-cols-9 gap-1.5 p-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200/70 dark:border-gray-700">
                                <template x-for="item in iconCatalog" :key="item.id">
                                    <button type="button"
                                            @click="newStep.icon = item.id"
                                            :title="item.label"
                                            class="p-2.5 rounded-xl flex items-center justify-center transition-all cursor-pointer"
                                            :class="newStep.icon === item.id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 ring-2 ring-indigo-400 scale-105' : 'bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700'">
                                        <span class="w-4 h-4 flex items-center justify-center" x-html="item.svg"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Section 4: Dependencies Architecture --}}
                        <div
                            class="p-4 sm:p-5 rounded-2xl border border-amber-200/80 dark:border-amber-900/40 bg-amber-50/40 dark:bg-amber-950/20 space-y-3">
                            <div
                                class="flex items-center gap-2 pb-1.5 border-b border-amber-200/60 dark:border-amber-900/40">
                                <span
                                    class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-400 flex items-center justify-center text-xs font-black">۴</span>
                                <div>
                                    <h4 class="text-xs font-black text-amber-950 dark:text-amber-300">منطق وابستگی و
                                        جای‌گذاری هوشمند در تور</h4>
                                    <p class="text-xs text-amber-800/80 dark:text-amber-400/80">برای حفظ پیوستگی،
                                        گام پس از انتخاب پیش‌نیاز به‌صورت خودکار در موقعیت متوالی قرار می‌گیرد.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label
                                    class="flex items-center gap-2 p-3 rounded-xl border bg-white dark:bg-gray-800 cursor-pointer transition-all"
                                    :class="newStep.depends_on_mode === 'none' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-700'">
                                    <input type="radio" value="none" x-model="newStep.depends_on_mode"
                                           class="text-amber-600 focus:ring-amber-500">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">بدون پیش‌نیاز (آزاد)</span>
                                </label>

                                <label
                                    class="flex items-center gap-2 p-3 rounded-xl border bg-white dark:bg-gray-800 cursor-pointer transition-all"
                                    :class="newStep.depends_on_mode === 'previous' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-700'">
                                    <input type="radio" value="previous" x-model="newStep.depends_on_mode"
                                           class="text-amber-600 focus:ring-amber-500">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">پیوسته (بعد از گام قبلی)</span>
                                </label>

                                <label
                                    class="flex items-center gap-2 p-3 rounded-xl border bg-white dark:bg-gray-800 cursor-pointer transition-all"
                                    :class="newStep.depends_on_mode === 'specific' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-700'">
                                    <input type="radio" value="specific" x-model="newStep.depends_on_mode"
                                           class="text-amber-600 focus:ring-amber-500">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">گام مشخص</span>
                                </label>
                            </div>

                            <div x-show="newStep.depends_on_mode === 'specific'" class="pt-1">
                                <x-resellerhub::label>انتخاب مرحله پیش‌نیاز:</x-resellerhub::label>
                                <select x-model="newStep.specific_depends_on"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white mt-1">
                                    <option value="">-- لطفاً گام پیش‌نیاز را انتخاب کنید --</option>
                                    <template x-for="pStep in tourSteps" :key="pStep.slug">
                                        <option :value="pStep.slug"
                                                x-text="pStep.title + (pStep.is_core ? ' (سیستمی)' : ' (سفارشی)')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="pt-4 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between shrink-0">
                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">گام‌های سفارشی بلافاصله در ویجت داشبورد نمایندگان و کاربران فعال می‌شوند.</span>
                        <div class="flex items-center gap-2.5 mr-auto">
                            <button type="button" @click="addCustomStepModalOpen = false"
                                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                انصراف
                            </button>
                            <button type="button" @click="createCustomStep()"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-xs transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>ثبت و افزودن به مراحل تور</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @include('resellerhub::user.settings.tabs.partials.tour-modals')
</div>
