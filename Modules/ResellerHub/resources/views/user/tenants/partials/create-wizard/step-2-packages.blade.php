            <div x-show="currentStep === 2" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="p-6 sm:p-8 space-y-8 min-h-[420px]">

                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۲</span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">انتخاب خریدار سامانه (حساب
                                زیرمجموعه)</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">حساب خریدار این سامانه CRM را از
                                لیست
                                زیرمجموعه‌های فعال خود انتخاب فرمایید یا سریعاً یک زیرمجموعه جدید بسازید.</p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_account_id" x-bind:value="form.sub_account_id">
                <input type="hidden" name="admin_name" x-bind:value="form.admin_name">
                <input type="hidden" name="admin_email" x-bind:value="form.admin_email">
                <input type="hidden" name="admin_mobile" x-bind:value="form.admin_mobile">

                {{-- Reseller Quota Banner --}}
                <div
                    class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                        <span
                            x-text="(currentReseller?.name || '{{ $reseller->company_name ?: 'N' }}').charAt(0)"></span>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">نماینده مجری / حساب ثبت‌کننده:</div>
                            <div class="text-sm font-black text-gray-900 dark:text-white"
                                 x-text="currentReseller?.name || '{{ $reseller->company_name ?: ($reseller->user?->name ?? 'نماینده #' . $reseller->id) }}'"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span
                            class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-indigo-100 dark:border-indigo-900/60 font-bold text-emerald-600 dark:text-emerald-400">
                            <span>ظرفیت ساخت سامانه: </span>
                            <span>نامحدود</span>
                        </span>
                    </div>
                </div>

                {{-- Sub-Account Selector & Cards --}}
                <div class="space-y-5">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2.5">
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">حساب خریدار (زیرمجموعه
                                نماینده)</h3>
                            <span class="text-xs text-gray-400">یک خریدار می‌تواند مالک چندین CRM باشد</span>
                        </div>
                    </div>

                    <!-- Sub-Account Dropdown -->
                    <div x-data="{
                    open: false,
                    search: '',
                    get options() {
                        return (typeof subAccounts !== 'undefined' && Array.isArray(subAccounts)) ? subAccounts : [];
                    },
                    get filteredOptions() {
                        if (!this.search.trim()) return this.options;
                        const q = this.search.toLowerCase();
                        return this.options.filter(o =>
                            (o.name && o.name.toLowerCase().includes(q)) ||
                            (o.mobile && String(o.mobile).includes(q)) ||
                            (o.company_name && o.company_name.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        if (typeof selectSubAccount === 'function') {
                            selectSubAccount(opt);
                        } else {
                            form.sub_account_id = opt ? String(opt.id) : '';
                            form.admin_name = opt ? opt.name : '';
                            form.admin_email = opt ? opt.email : '';
                            form.admin_mobile = opt ? opt.mobile : '';
                        }
                        this.open = false;
                        this.search = '';
                        if (typeof errors !== 'undefined' && errors.sub_account_id) { delete errors.sub_account_id; }
                    },
                    getSelectedTitle() {
                        let found = this.options.find(o => String(o.id) === String(form.sub_account_id));
                        if (!found && typeof selectedSubAccount !== 'undefined' && selectedSubAccount) {
                            found = selectedSubAccount;
                        }
                        if (!found) return 'انتخاب خریدار...';
                        let title = found.name;
                        if (found.company_name) title += ' (' + found.company_name + ')';
                        return title;
                    },
                    formatFa(str) {
                        return window.faNum ? window.faNum(str) : (str !== null && str !== undefined ? String(str) : '');
                    }
                }" class="relative overflow-visible" :class="{ 'z-50': open }">
                        <button type="button" @click="open = !open"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50/70 dark:bg-gray-900/50 dark:border-gray-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all cursor-pointer flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                            <div class="flex items-center gap-1.5">
                                <div
                                    class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                                    <span x-text="getSelectedTitle() ? getSelectedTitle().charAt(0) : 'U'"></span>
                                </div>
                                <span class="truncate font-medium" x-text="getSelectedTitle()"></span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute z-[9999] left-0 right-0 top-full mt-2 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto">
                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="search"
                                       placeholder="جستجوی خریدار بر اساس نام، شماره موبایل یا نام مرکز/برند..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            <template x-for="opt in filteredOptions" :key="opt.id">
                                <div @click="select(opt)"
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                     :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(form.sub_account_id) === String(opt.id) }">
                                    <div class="flex items-center gap-1.5">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">
                                            <span x-text="opt.name ? opt.name.charAt(0) : 'U'"></span>
                                        </div>
                                        <div class="truncate">
                                            <span x-text="opt.name" class="font-medium"></span>
                                            <template x-if="opt.company_name">
                                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400"
                                                  x-text="'(' + opt.company_name + ')'"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ زیرمجموعه‌ای یافت نشد
                            </div>
                        </div>
                    </div>

                    <!-- Error message -->
                    <template x-if="errors.sub_account_id">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.sub_account_id[0]"></p>
                    </template>

                    <button type="button" @click="openQuickSubModal()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>افزودن زیرمجموعه جدید</span>
                    </button>
                </div>

                {{-- Step 2 Navigation Buttons --}}
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                    <button type="button" @click="prevStep()"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>مرحله قبلی: آدرس و ظاهر</span>
                    </button>

                    <button type="button" @click="nextStep()"
                            :disabled="!form.sub_account_id"
                            class="inline-flex items-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-black rounded-xl transition shadow-lg shadow-indigo-600/25 cursor-pointer">
                        <span>مرحله بعدی: انتخاب پکیج و لایسنس</span>
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>

