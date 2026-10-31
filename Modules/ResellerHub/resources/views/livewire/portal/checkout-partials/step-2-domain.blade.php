<div
    class="mx-auto max-w-5xl sm:max-w-6xl rounded-3xl border border-gray-200/90 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-8 shadow-xl shadow-gray-200/30 dark:shadow-none">
    {{-- Header --}}
    <div class="flex items-start gap-3.5 mb-6">
        <div
            class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">آدرس و دامنه
                دسترسی به سامانه</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">آدرسی که شما و همکارانتان برای
                ورود به پنل CRM از آن استفاده خواهید کرد.</p>
        </div>
    </div>

    {{-- Domain Type Toggle Tabs --}}
    <div
        class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-2xl mb-6">
        <button type="button"
                wire:click="setDomainType('subdomain')"
                class="py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all text-center flex items-center justify-center gap-2 cursor-pointer {{ $domainType === 'subdomain' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span>{{ __('resellerhub::messages.domain_branding.subdomain_title') }}</span>
        </button>
        <button type="button"
                wire:click="setDomainType('custom_domain')"
                class="py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all text-center flex items-center justify-center gap-2 cursor-pointer {{ $domainType === 'custom_domain' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
            <span>{{ __('resellerhub::messages.domain_branding.custom_domain_title') }}</span>
        </button>
    </div>

    @if($domainType === 'subdomain')
        {{-- Subdomain / Path Input Form (Balanced 2-column layout on desktop) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-2">
                <label class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                    شناسه و مسیر اختصاصی سامانه: <span class="text-red-500 font-bold">*</span>
                </label>
                <div dir="ltr"
                     class="relative flex items-center rounded-xl border {{ $domainError ? 'border-red-300 dark:border-red-800 ring-2 ring-red-500/10' : ($domainSuccess ? 'border-emerald-300 dark:border-emerald-800 ring-2 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-700 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20') }} bg-gray-50/50 dark:bg-gray-800/50 transition-all overflow-hidden h-12">
                                    <span
                                        class="bg-gray-100 dark:bg-gray-800 px-4 h-full flex items-center text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 shrink-0 select-none"
                                        dir="ltr">
                                        {{ $baseDomain }}/
                                    </span>
                    <input type="text"
                           wire:model.live.debounce.400ms="subdomain"
                           dir="ltr"
                           class="block flex-1 min-w-0 border-0 bg-transparent py-2.5 px-4 text-sm sm:text-base font-bold text-gray-900 placeholder-gray-400 focus:ring-0 dark:text-white text-left"
                           placeholder="company">
                </div>
                @if($domainError)
                    <p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $domainError }}</span>
                    </p>
                @elseif($domainSuccess)
                    <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $domainSuccess }}</span>
                    </p>
                @else
                    <p class="mt-1.5 text-xs text-gray-400">فقط شامل حروف کوچک انگلیسی، اعداد و خط
                        تیره (-). حداقل ۳ کاراکتر.</p>
                @endif
            </div>

            {{-- Live Preview URL Card on Desktop Side Column --}}
            <div class="lg:col-span-5">
                <div
                    class="rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 p-4 border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                                    <span
                                        class="text-xs font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        پیش‌نمایش آدرس ورود مستقیم به سامانه:
                                    </span>
                    <div
                        class="font-black text-indigo-600 dark:text-indigo-400 dir-ltr text-xs sm:text-sm bg-white dark:bg-gray-800 px-3.5 py-2.5 rounded-xl border border-indigo-100 dark:border-indigo-900/60 select-all truncate">
                        {{ $urlScheme }}{{ $baseDomain }}/{{ $subdomain ? $subdomain : 'company' }}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">سامانه بدون نیاز به تنظیمات
                        DNS، بلافاصله آماده استفاده خواهد بود.</p>
                </div>
            </div>
        </div>
    @else
        {{-- Custom Domain Form (Balanced 2-column layout on desktop) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-4">
                <div>
                    <label
                        class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        نام دامنه اختصاصی شما: <span class="text-red-500 font-bold">*</span>
                    </label>
                    <div dir="ltr"
                         class="relative flex items-center rounded-xl border {{ $customDomainError ? 'border-red-300 dark:border-red-800 ring-2 ring-red-500/10' : ($customDomainSuccess ? 'border-emerald-300 dark:border-emerald-800 ring-2 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-700 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20') }} bg-gray-50/50 dark:bg-gray-800/50 transition-all overflow-hidden h-12">
                                        <span
                                            class="bg-gray-100 dark:bg-gray-800 px-4 h-full flex items-center text-xs text-gray-400 border-r border-gray-200 dark:border-gray-700 shrink-0 select-none"
                                            dir="ltr">{{ $urlScheme }}</span>
                        <input type="text"
                               wire:model.live.debounce.400ms="customDomain"
                               dir="ltr"
                               class="block flex-1 min-w-0 border-0 bg-transparent py-2.5 px-4 text-sm sm:text-base font-bold text-gray-900 placeholder-gray-400 focus:ring-0 dark:text-white text-left"
                               placeholder="crm.mycompany.ir یا company.com">
                    </div>
                    @if($customDomainError)
                        <p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $customDomainError }}</span>
                        </p>
                    @elseif($customDomainSuccess)
                        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $customDomainSuccess }}</span>
                        </p>
                    @endif
                </div>

                {{-- Internal Cloud Subdomain (Backup) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">شناسه
                        و مسیر ابری پشتیبان سامانه:</label>
                    <div dir="ltr"
                         class="relative flex items-center rounded-xl border {{ $domainError ? 'border-red-300 dark:border-red-800 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-700' }} bg-gray-50/50 dark:bg-gray-800/50 overflow-hidden h-10">
                                        <span
                                            class="bg-gray-100 dark:bg-gray-800 px-3 h-full flex items-center text-xs font-bold text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 shrink-0 select-none"
                                            dir="ltr">
                                            {{ $baseDomain }}/
                                        </span>
                        <input type="text"
                               wire:model.live.debounce.400ms="subdomain"
                               dir="ltr"
                               class="block flex-1 min-w-0 border-0 bg-transparent py-2 px-3 text-xs font-medium text-gray-900 focus:ring-0 dark:text-white text-left"
                               placeholder="mycompany">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">حتی در صورت تاخیر در تنظیم DNS دامنه،
                        همواره از این آدرس ابری دسترسی خواهید داشت.</p>
                    @if($domainError)
                        <p class="mt-1 text-xs text-red-600">{{ $domainError }}</p>
                    @endif
                </div>
            </div>

            {{-- DNS Instruction Notice on Desktop Side Column --}}
            <div class="lg:col-span-5">
                <div
                    class="rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 p-4 space-y-2.5">
                    <div
                        class="flex items-center gap-2 text-xs font-bold text-amber-800 dark:text-amber-300">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>راهنمای تنظیم رکورد DNS:</span>
                    </div>
                    <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                        در کنترل‌پنل مدیریت دامنه خود (کلودفلر، ابرآروان، یا پنل هاست)، رکورد زیر را ثبت
                        فرمایید:
                    </p>
                    <div
                        class="p-3 bg-white/80 dark:bg-gray-900/60 rounded-xl border border-amber-200/60 dark:border-amber-800/40 text-xs space-y-1.5"
                        dir="ltr">
                        <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span class="font-bold">CNAME:</span>
                            <span
                                class="font-bold text-indigo-600 dark:text-indigo-400">@ یا crm &rarr; {{ $targetCname ?: $baseDomain }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between text-gray-500 dark:text-gray-400 text-xs pt-1 border-t border-gray-100 dark:border-gray-800">
                            <span>یا A Record:</span>
                            <span>@ &rarr; {{ $serverIp }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
