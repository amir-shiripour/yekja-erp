        <div x-show="currentStep === 2" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="p-6 sm:p-8 space-y-8">

            {{-- Hidden inputs to pass data with form submission --}}
            <input type="hidden" name="sub_account_id" :value="form.sub_account_id">
            <input type="hidden" name="admin_name" :value="form.admin_name">
            <input type="hidden" name="admin_email" :value="form.admin_email">
            <input type="hidden" name="admin_mobile" :value="form.admin_mobile">

            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span
                        class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center">۲</span>
                    <div>
                        <h2 class="text-base font-black text-gray-900 dark:text-white">تخصیص حساب زیرمجموعه (خریدار
                            سامانه) و وضعیت دسترسی</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشخص کنید این سامانه متعلق به کدام
                            حساب زیرمجموعه (مشتری) شماست. یک زیرمجموعه می‌تواند چندین سامانه CRM داشته باشد.</p>
                    </div>
                </div>

                <button type="button" @click="openQuickSubModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-xs font-bold transition shadow-2xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>افزودن حساب جدید</span>
                </button>
            </div>

            {{-- Sub-Account Selector Section --}}
            <div class="space-y-4">
                {{-- Selected Sub-Account Card --}}
                <template x-if="selectedSubAccount">
                    <div
                        class="p-5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 shadow-sm relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-indigo-600/20 shrink-0">
                                    <span
                                        x-text="selectedSubAccount.name ? selectedSubAccount.name.charAt(0) : 'U'"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white truncate"
                                            x-text="selectedSubAccount.name"></h3>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                            <span>مالک فعلی سامانه</span>
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-1 flex-wrap">
                                        <span class="flex items-center gap-1 dir-ltr text-right">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                           stroke-linejoin="round" stroke-width="2"
                                                                           d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span x-text="selectedSubAccount.mobile"></span>
                                        </span>
                                        <template x-if="selectedSubAccount.company_name">
                                            <span
                                                class="flex items-center gap-1 text-gray-600 dark:text-gray-300 font-medium">
                                                <span>•</span>
                                                <span x-text="selectedSubAccount.company_name"></span>
                                            </span>
                                        </template>
                                        <span
                                            class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                            <span>•</span>
                                            <span
                                                x-text="faNum(selectedSubAccount.tenants_count || 0) + ' سامانه فعال'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="clearSubAccount()"
                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-900/50 text-xs font-bold transition shadow-2xs cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>تغییر حساب زیرمجموعه</span>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Sub-Account Picker (When not selected or changing) --}}
                <template x-if="!selectedSubAccount">
                    <div class="space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <x-resellerhub::label>
                                انتخاب خریدار (زیرمجموعه) از لیست موجود <span class="text-red-500 font-bold">*</span>
                            </x-resellerhub::label>
                            {{-- Search Filter Input --}}
                            <div class="relative max-w-xs w-full">
                                <input type="text" x-model="subAccountSearchQuery"
                                       placeholder="جستجوی نام یا شماره تماس مشتری..."
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/60 px-3.5 py-2 ps-9 text-xs font-medium text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10">
                                <svg
                                    class="w-4 h-4 text-gray-400 absolute inset-y-0 start-3 my-auto pointer-events-none"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Sub-Accounts Cards Grid --}}
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto custom-scrollbar p-1">
                            <template x-for="sub in filteredSubAccounts()" :key="sub.id">
                                <div @click="selectSubAccount(sub)"
                                     class="p-4 rounded-2xl border transition-all cursor-pointer select-none space-y-2.5 bg-white dark:bg-gray-800/90 border-gray-200/80 dark:border-gray-700/80 hover:border-indigo-500 hover:shadow-md group">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            <span x-text="sub.name ? sub.name.charAt(0) : 'U'"></span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                                x-text="sub.name"></h4>
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-gray-400 dir-ltr text-right mt-0.5"
                                                x-text="sub.mobile"></div>
                                            <template x-if="sub.company_name">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5"
                                                   x-text="sub.company_name"></p>
                                            </template>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                        <span class="text-gray-400"
                                              x-text="faNum(sub.tenants_count || 0) + ' سامانه CRM'"></span>
                                        <span
                                            class="font-bold text-indigo-600 dark:text-indigo-400 group-hover:translate-x-[-2px] transition-transform flex items-center gap-1">
                                            <span>انتخاب</span>
                                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                           stroke-linejoin="round" stroke-width="2"
                                                                           d="M9 5l7 7-7 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Empty State --}}
                        <template x-if="filteredSubAccounts().length === 0">
                            <div
                                class="text-center py-10 px-4 rounded-3xl bg-gray-50/60 dark:bg-gray-900/30 border border-dashed border-gray-200 dark:border-gray-700 space-y-3">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200">
                                    حساب زیرمجموعه‌ای یافت نشد
                                </h4>
                                <p class="text-xs text-gray-400 max-w-sm mx-auto">
                                    هیچ حسابی مطابق با عبارت جستجو پیدا نشد. برای تعریف سریع خریدار روی دکمه زیر کلیک
                                    فرمایید.
                                </p>
                                <button type="button" @click="openQuickSubModal()"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>افزودن حساب زیرمجموعه جدید</span>
                                </button>
                            </div>
                        </template>

                        <template x-if="errors.sub_account_id">
                            <p class="text-xs text-red-500 font-bold mt-1" x-text="errors.sub_account_id[0]"></p>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Status Selector --}}
            <div class="pt-5 border-t border-gray-100 dark:border-gray-700/60">
                <div class="max-w-md space-y-1.5">
                    <x-resellerhub::label>
                        وضعیت دسترسی سامانه <span class="text-red-500 font-bold">*</span>
                    </x-resellerhub::label>
                    <x-resellerhub::select name="status" x-model="form.status">
                        <option value="active">فعال (دسترسی عادی به پنل CRM)</option>
                        <option value="suspended">معلق شده (قطع موقت دسترسی سامانه)</option>
                        <option value="expired">منقضی شده (پایان اعتبار اشتراک)</option>
                    </x-resellerhub::select>
                    <p class="text-xs text-gray-400 mt-1">در صورت قرار گرفتن در حالت معلق یا منقضی، کاربران سامانه
                        دسترسی نخواهند داشت.</p>
                    <template x-if="errors.status">
                        <p class="text-xs text-red-500 mt-1" x-text="errors.status[0]"></p>
                    </template>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <button type="button" @click="prevStep()"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>مرحله قبلی: کسب‌وکار</span>
                </button>

                <button type="button" @click="nextStep()"
                        :disabled="!form.sub_account_id"
                        class="inline-flex items-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-black rounded-xl transition shadow-lg shadow-indigo-600/25 cursor-pointer">
                    <span>مرحله بعدی: ماژول‌ها و ذخیره</span>
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </div>
