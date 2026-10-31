<div x-show="suspendModalOpen"
     x-cloak
     class="fixed inset-0 z-[9999] overflow-y-auto"
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-gray-950/75 backdrop-blur-md transition-opacity"
             @click="!loading && closeModal()"></div>

        <div
            class="relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full z-10 border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6 text-center">

            {{-- Dynamic Icon Badge --}}
            <div
                class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center shadow-lg transition-colors duration-300"
                :class="selectedTenant.suspended ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shadow-emerald-500/20' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shadow-amber-500/20'">
                <template x-if="selectedTenant.suspended">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </template>
                <template x-if="!selectedTenant.suspended">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                    </svg>
                </template>
            </div>

            {{-- Title & Body --}}
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900 dark:text-white"
                    x-text="selectedTenant.suspended ? ('رفع مسدودیت سامانه CRM ' + (selectedTenant.name || '')) : ('مسدودسازی سامانه CRM ' + (selectedTenant.name || ''))"></h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed"
                   x-text="selectedTenant.suspended ? 'با رفع مسدودیت، دسترسی کاربران و مشتریان به این سامانه CRM و سابدامین اختصاصی مجدداً فعال خواهد شد.' : 'با مسدود شدن (Suspend)، دسترسی کاربران به این سامانه CRM موقتاً قطع شده و امکان ورود به پنل را نخواهند داشت.'"></p>
            </div>

            {{-- Tenant Details Card --}}
            <div
                class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-2.5 text-start">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">نام کسب‌وکار:</span>
                    <span class="font-black text-gray-800 dark:text-gray-200"
                          x-text="selectedTenant.name || '—'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold">آدرس سابدامین:</span>
                    <span class="font-black text-indigo-600 dark:text-indigo-400" dir="ltr"
                          x-text="selectedTenant.subdomain || '—'"></span>
                </div>
            </div>

            {{-- Modal Action Buttons --}}
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="closeModal()" :disabled="loading"
                        class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold transition-all w-1/2 flex items-center justify-center cursor-pointer disabled:opacity-50">
                    انصراف
                </button>
                <button type="button" @click="confirmSuspend()" :disabled="loading"
                        :class="selectedTenant.suspended ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/25' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-500/25'"
                        class="px-5 py-3 rounded-2xl text-white text-sm font-black transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 w-1/2 disabled:opacity-50 cursor-pointer">
                    <template x-if="loading">
                        <svg class="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="!loading && selectedTenant.suspended">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="!loading && !selectedTenant.suspended">
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 7a2 2 0 114 0v4h-4V7z"/>
                        </svg>
                    </template>
                    <span
                        x-text="loading ? 'در حال پردازش...' : (selectedTenant.suspended ? 'تایید و فعال‌سازی' : 'تایید و مسدودسازی')"></span>
                </button>
            </div>
        </div>
    </div>
</div>
