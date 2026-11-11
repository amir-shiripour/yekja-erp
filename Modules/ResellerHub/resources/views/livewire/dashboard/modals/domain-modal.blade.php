@if ($showDomainModal)
    @php
        $displayHost = request()->getHost() ?: 'crm-laravel.test';
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
         x-data="{
             slug: @entangle('subdomainInput').live,
             get cleanSlug() {
                 return (this.slug || '').toString().toLowerCase().trim();
             }
         }">
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-xs transition-opacity"
             wire:click="closeDomainModal"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-200/80 dark:border-gray-700/80">

                {{-- Modal Header --}}
                <div
                    class="p-6 bg-gray-50/80 dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <span
                            class="flex items-center justify-center w-11 h-11 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/25 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                تنظیم دامین دلخواه سامانه
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                تعیین نشانی اختصاصی سامانه یا اتصال دامنه مستقل (Custom Domain)
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDomainModal"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Form --}}
                <form wire:submit="saveDomain">
                    <div class="p-6 space-y-5">
                        @if (!empty($allowCustomDomains))
                            {{-- Domain Mode Toggle Tabs --}}
                            <div
                                class="flex p-1 bg-gray-100 dark:bg-gray-900/60 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                                <button
                                    type="button"
                                    wire:click="$set('domainType', 'subdomain')"
                                    class="flex-1 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer {{ $domainType === 'subdomain' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                >
                                    نشانی اختصاصی ابری (پیش‌فرض)
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('domainType', 'custom_domain')"
                                    class="flex-1 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer {{ $domainType === 'custom_domain' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                >
                                    دامنه اختصاصی (Custom Domain)
                                </button>
                            </div>
                        @endif

                        @if (empty($allowCustomDomains) || $domainType === 'subdomain')
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                            نام و شناسه اختصاصی سامانه:
                                        </label>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">
                                            فقط حروف انگلیسی، عدد و خط تیره (-)
                                        </span>
                                    </div>

                                    {{-- Input with Host Prefix (crm-laravel.test/ or host/) and empty input --}}
                                    <div
                                        class="flex items-center rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/50 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-900 overflow-hidden shadow-xs transition-all"
                                        dir="ltr">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3.5 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-mono font-bold border-r border-gray-200 dark:border-gray-700 select-none shrink-0">
                                            <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                            </svg>
                                            {{ $displayHost }}/
                                        </span>
                                        <input
                                            type="text"
                                            x-model="slug"
                                            placeholder="my-company"
                                            class="flex-1 px-3.5 py-3 bg-transparent text-gray-900 dark:text-white text-xs outline-none font-mono font-medium text-left placeholder:text-gray-400"
                                            autofocus
                                        />
                                    </div>

                                    @error('subdomainInput')
                                    <div class="flex items-center gap-1.5 text-xs text-red-500 font-semibold mt-1">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </div>
                                    @enderror
                                </div>

                                {{-- Live Preview Section (بخش زنده پیش‌نمایش آدرس سامانه) --}}
                                <div
                                    class="rounded-xl p-4 bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 shadow-2xs space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2.5 w-2.5">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                                                    x-show="cleanSlug"></span>
                                                <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                                      :class="cleanSlug ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'"></span>
                                            </span>
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                پیش‌نمایش زنده نشانی سامانه شما
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-all"
                                              :class="cleanSlug ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400'">
                                            <span x-show="cleanSlug">نشانی معتبر</span>
                                            <span x-show="!cleanSlug">در انتظار ورود نام</span>
                                        </span>
                                    </div>

                                    {{-- Address Bar Look --}}
                                    <div
                                        class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-mono shadow-inner overflow-x-auto"
                                        dir="ltr">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span class="text-gray-400 dark:text-gray-500 text-[11px] select-none">https://</span>
                                        <span class="text-gray-700 dark:text-gray-200 font-semibold select-none">{{ $displayHost }}/</span>
                                        <span class="font-black text-indigo-600 dark:text-indigo-400 tracking-wide"
                                              x-text="cleanSlug || 'my-company'"></span>
                                        <span class="text-gray-400 dark:text-gray-600 text-[11px] select-none">/user/dashboard</span>
                                    </div>

                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed text-right">
                                        این نشانی آدرس مستقیم سامانه شماست که مدیران و کارکنان با مراجعه به آن وارد
                                        پنل کاری می‌شوند.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        آدرس دامنه مستقل شما (بدون http/https):
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="customDomainInput"
                                        placeholder="crm.yourbrand.ir"
                                        dir="ltr"
                                        class="w-full px-3.5 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                    />
                                    @error('customDomainInput')
                                    <div class="flex items-center gap-1.5 text-xs text-red-500 font-semibold mt-1">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </div>
                                    @enderror
                                </div>

                                {{-- CNAME Guide Notice Box --}}
                                <div
                                    class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-right space-y-2">
                                    <div
                                        class="flex items-center gap-2 text-amber-800 dark:text-amber-300 text-xs font-bold">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>راهنمای اتصال DNS دامنه اختصاصی</span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                        برای فعال‌سازی دامنه، در پنل مدیریت DNS دامنه خود یک رکورد از نوع <span
                                            class="font-bold text-amber-700 dark:text-amber-400">CNAME</span> به سمت
                                        مقدار زیر تنظیم کنید:
                                    </p>
                                    <div
                                        class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-900 rounded-xl border border-amber-200 dark:border-amber-900 font-mono text-xs text-gray-800 dark:text-gray-200"
                                        dir="ltr">
                                        <span>{{ $cnameTarget }}</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">CNAME</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="p-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            wire:click="closeDomainModal"
                            class="px-4 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200/60 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer"
                        >
                            انصراف
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
                        >
                            <span>ذخیره و تایید دامین</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
