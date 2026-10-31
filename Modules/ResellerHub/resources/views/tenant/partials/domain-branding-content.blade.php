@php
    use App\Tenancy\TenantManager;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Services\DomainVerificationService;
    use Modules\ResellerHub\App\Models\ResellerSetting;

    /** @var TenantManager $tenantManager */
    $tenantManager = app(TenantManager::class);
    $activeTenant = $activeTenant ?? ($tenantManager->getTenant() ?: \App\Models\Tenant::find(auth()->user()?->tenant_id));

    if ($activeTenant) {
        $activeTenant->loadMissing(['reseller.user', 'package']);
    }

    $allowsCustomDomain = $activeTenant ? $activeTenant->allowsCustomDomain() : false;
    $allowsWhiteLabel = $activeTenant ? $activeTenant->allowsWhiteLabel() : false;

    $branding = is_array($activeTenant?->branding) ? $activeTenant->branding : [];
    $reseller = $activeTenant?->reseller;
    $resellerSettings = $reseller ? ResellerSetting::allForReseller($reseller->id) : [];

    $domainService = app(DomainVerificationService::class);
    $cnameTarget = $domainService->getTargetCname();

    $currentCustomDomain = $activeTenant?->custom_domain ?? '';
    $currentSubdomain = $activeTenant?->subdomain ?? '';
    $tenantUrl = $activeTenant ? $tenantManager->getTenantUrl($activeTenant) : url('/');

    $brandTitle = $branding['brand_title'] ?? ($activeTenant?->name ?? '');
    $brandPrimaryColor = $branding['brand_primary_color'] ?? ($resellerSettings['brand_primary_color'] ?? '#4f46e5');
    $brandFooterText = $branding['brand_footer_text'] ?? '';
    $supportPhone = $branding['support_phone'] ?? ($reseller?->support_phone ?? '');
    $supportEmail = $branding['support_email'] ?? ($reseller?->support_email ?? '');
    $brandLogo = $branding['brand_logo'] ?? null;
    $brandFavicon = $branding['brand_favicon'] ?? null;

    $brandLogoUrl = $brandLogo ? asset('storage/' . $brandLogo) : null;
    $brandFaviconUrl = $brandFavicon ? asset('storage/' . $brandFavicon) : null;
@endphp

@if(!$activeTenant)
    <div class="p-8 text-center rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-500">
        {{ __('resellerhub::messages.domain_branding.tenant_not_found') }}
    </div>
@else
    <div x-data="tenantDomainBrandingHandler()" class="space-y-8">

        {{-- Top Summary Header --}}
        <div
            class="rounded-3xl p-6 sm:p-7 bg-gradient-to-br from-indigo-50/70 via-white to-purple-50/40 dark:from-gray-800/90 dark:via-gray-800/80 dark:to-indigo-950/30 border border-indigo-100/90 dark:border-gray-700/80 shadow-xs relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span>{{ __('resellerhub::messages.domain_branding.header_title') }}</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('resellerhub::messages.domain_branding.header_desc') }}
                        </p>
                    </div>
                </div>

                {{-- Status Pills --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $allowsCustomDomain ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                        <span
                            class="w-2 h-2 rounded-full {{ $allowsCustomDomain ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        <span>{{ __('resellerhub::messages.domain_branding.badge_custom_domain', ['status' => $allowsCustomDomain ? __('resellerhub::messages.domain_branding.status_active') : __('resellerhub::messages.domain_branding.status_disabled_in_plan')]) }}</span>
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $allowsWhiteLabel ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                        <span
                            class="w-2 h-2 rounded-full {{ $allowsWhiteLabel ? 'bg-indigo-500' : 'bg-gray-400' }}"></span>
                        <span>{{ __('resellerhub::messages.domain_branding.badge_white_label', ['status' => $allowsWhiteLabel ? __('resellerhub::messages.domain_branding.status_active') : __('resellerhub::messages.domain_branding.status_disabled_in_plan')]) }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Grid: Left (Domain Management) / Right (White-Label Branding) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- 1. DOMAIN SECTION (5 cols on lg) --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- Active Subdomain Card --}}
                <div
                    class="rounded-3xl p-6 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.domain_branding.subdomain_title') }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('resellerhub::messages.domain_branding.subdomain_desc') }}</p>
                            </div>
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                            {{ __('resellerhub::messages.domain_branding.status_active') }}
                        </span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span
                                class="text-xs font-bold text-gray-400 block">{{ __('resellerhub::messages.domain_branding.direct_login_url') }}</span>
                            <span
                                class="text-xs sm:text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400 dir-ltr block truncate">
                                {{ $currentSubdomain ? $currentSubdomain . '.' . (parse_url(url('/'), PHP_URL_HOST) ?: 'crm.test') : $tenantUrl }}
                            </span>
                        </div>
                        <a href="{{ $tenantUrl }}" target="_blank"
                           title="{{ __('resellerhub::messages.domain_branding.view_url') }}"
                           class="w-9 h-9 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all shadow-2xs shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Custom Domain Card (Conditional: Active vs Locked) --}}
                <div
                    class="rounded-3xl p-6 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-5 relative overflow-hidden">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="p-2 rounded-xl {{ $allowsCustomDomain ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.domain_branding.custom_domain_title') }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('resellerhub::messages.domain_branding.custom_domain_desc') }}</p>
                        </div>
                    </div>

                    @if($allowsCustomDomain)
                        {{-- Form: Custom Domain Enabled --}}
                        <form @submit.prevent="saveCustomDomain" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    {{ __('resellerhub::messages.domain_branding.custom_domain_label') }}
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           x-model="customDomainInput"
                                           dir="ltr"
                                           placeholder="{{ __('resellerhub::messages.domain_branding.custom_domain_placeholder') }}"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono transition-all">
                                </div>
                                <span
                                    class="text-xs text-gray-400 block mt-1">{{ __('resellerhub::messages.domain_branding.custom_domain_hint') }}</span>
                            </div>

                            {{-- CNAME Instructions Box --}}
                            <div
                                class="p-3.5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/50 space-y-2 text-xs leading-relaxed text-amber-900 dark:text-amber-200">
                                <div class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ __('resellerhub::messages.domain_branding.cname_box_title') }}</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ __('resellerhub::messages.domain_branding.cname_box_desc') }}
                                </p>
                                <div
                                    class="p-2 rounded-xl bg-white dark:bg-gray-900/80 border border-amber-200/60 dark:border-amber-900/60 font-mono text-center text-xs font-black text-indigo-600 dark:text-indigo-400 dir-ltr select-all">
                                    {{ $cnameTarget }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <button type="submit"
                                        :disabled="domainSubmitting"
                                        class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 cursor-pointer disabled:opacity-50 inline-flex items-center justify-center gap-2 transition-all">
                                    <template x-if="domainSubmitting">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span
                                        x-text="domainSubmitting ? checkingAndSavingText : connectDomainBtnText"></span>
                                </button>

                                <template x-if="customDomainInput">
                                    <button type="button"
                                            @click="disconnectCustomDomain"
                                            :disabled="domainSubmitting"
                                            title="{{ __('resellerhub::messages.domain_branding.disconnect_domain_title') }}"
                                            class="px-3 py-2.5 rounded-xl border border-rose-200 dark:border-rose-800/80 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold cursor-pointer disabled:opacity-50 transition-all">
                                        {{ __('resellerhub::messages.domain_branding.disconnect_domain_btn') }}
                                    </button>
                                </template>
                            </div>
                        </form>
                    @else
                        {{-- Locked State Banner --}}
                        <div
                            class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 mx-auto flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h4 class="text-xs font-black text-gray-800 dark:text-gray-200">{{ __('resellerhub::messages.domain_branding.custom_domain_locked_title') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('resellerhub::messages.domain_branding.custom_domain_locked_desc') }}
                            </p>
                            <button type="button"
                                    @click="activeTab = 'subscription'"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                <span>{{ __('resellerhub::messages.domain_branding.upgrade_plan_btn') }}</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. WHITE-LABEL BRANDING SECTION (7 cols on lg) --}}
            <div class="lg:col-span-7">
                <div
                    class="rounded-3xl p-6 sm:p-7 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-6 relative overflow-hidden">
                    <div
                        class="flex items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <span
                                class="p-2.5 rounded-2xl {{ $allowsWhiteLabel ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/60' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.domain_branding.whitelabel_title') }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('resellerhub::messages.domain_branding.whitelabel_desc') }}</p>
                            </div>
                        </div>

                        @if($allowsWhiteLabel)
                            <span
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                {{ __('resellerhub::messages.domain_branding.whitelabel_active_badge') }}
                            </span>
                        @endif
                    </div>

                    @if($allowsWhiteLabel)
                        {{-- White-Label Form --}}
                        <form @submit.prevent="saveBranding" class="space-y-6">
                            {{-- Row 1: Brand Title & Primary Color --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                        {{ __('resellerhub::messages.domain_branding.brand_title_label') }} <span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="brandTitle"
                                           required
                                           placeholder="{{ __('resellerhub::messages.domain_branding.brand_title_placeholder') }}"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                        {{ __('resellerhub::messages.domain_branding.brand_color_label') }} <span
                                            class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex items-center flex-1">
                                            <input type="text"
                                                   x-model="brandPrimaryColor"
                                                   dir="ltr"
                                                   placeholder="#4f46e5"
                                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 ps-10 pe-3.5 py-2.5 text-xs text-gray-900 dark:text-white font-mono placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                            <span
                                                class="absolute start-2.5 w-6 h-6 rounded-lg border border-gray-200 dark:border-gray-700 shadow-2xs shrink-0"
                                                :style="'background-color: ' + brandPrimaryColor"></span>
                                        </div>
                                        <input type="color"
                                               x-model="brandPrimaryColor"
                                               class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 p-0.5 bg-white dark:bg-gray-800 cursor-pointer shrink-0">
                                    </div>
                                    {{-- Color Presets --}}
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <template x-for="hex in colorPresets" :key="hex">
                                            <button type="button"
                                                    @click="brandPrimaryColor = hex"
                                                    :style="'background-color: ' + hex"
                                                    :class="brandPrimaryColor === hex ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' : 'opacity-80 hover:opacity-100 hover:scale-105'"
                                                    class="w-5 h-5 rounded-full transition-all cursor-pointer shadow-2xs"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Row 2: Logo and Favicon Uploads --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                {{-- Brand Logo --}}
                                <div
                                    class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-3">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        {{ __('resellerhub::messages.domain_branding.logo_label') }}
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center p-1 relative overflow-hidden shadow-2xs shrink-0">
                                            <template x-if="logoPreviewUrl">
                                                <img :src="logoPreviewUrl" alt="Logo Preview"
                                                     class="max-w-full max-h-full object-contain">
                                            </template>
                                            <template x-if="!logoPreviewUrl">
                                                <span
                                                    class="text-xs text-gray-400 font-bold">{{ __('resellerhub::messages.domain_branding.no_logo') }}</span>
                                            </template>
                                        </div>

                                        <div class="min-w-0 flex-1 space-y-1.5">
                                            <input type="file"
                                                   @change="handleLogoChange($event)"
                                                   accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                                   class="block w-full text-xs text-gray-500 file:me-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                                            <span
                                                class="text-xs text-gray-400 block">{{ __('resellerhub::messages.domain_branding.logo_hint') }}</span>
                                            <template x-if="logoPreviewUrl">
                                                <button type="button"
                                                        @click="removeLogo"
                                                        class="text-xs font-bold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                    {{ __('resellerhub::messages.domain_branding.remove_logo_btn') }}
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Brand Favicon --}}
                                <div
                                    class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 space-y-3">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        {{ __('resellerhub::messages.domain_branding.favicon_label') }}
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center p-2 relative overflow-hidden shadow-2xs shrink-0">
                                            <template x-if="faviconPreviewUrl">
                                                <img :src="faviconPreviewUrl" alt="Favicon Preview"
                                                     class="w-8 h-8 object-contain">
                                            </template>
                                            <template x-if="!faviconPreviewUrl">
                                                <span
                                                    class="text-xs text-gray-400 font-bold">{{ __('resellerhub::messages.domain_branding.no_favicon') }}</span>
                                            </template>
                                        </div>

                                        <div class="min-w-0 flex-1 space-y-1.5">
                                            <input type="file"
                                                   @change="handleFaviconChange($event)"
                                                   accept="image/x-icon,image/png,image/svg+xml"
                                                   class="block w-full text-xs text-gray-500 file:me-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                                            <span
                                                class="text-xs text-gray-400 block">{{ __('resellerhub::messages.domain_branding.favicon_hint') }}</span>
                                            <template x-if="faviconPreviewUrl">
                                                <button type="button"
                                                        @click="removeFavicon"
                                                        class="text-xs font-bold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                    {{ __('resellerhub::messages.domain_branding.remove_favicon_btn') }}
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Row 3: Support Info & Footer --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                        {{ __('resellerhub::messages.domain_branding.support_phone_label') }}
                                    </label>
                                    <input type="text"
                                           x-model="supportPhone"
                                           dir="ltr"
                                           placeholder="02188888888"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                        {{ __('resellerhub::messages.domain_branding.support_email_label') }}
                                    </label>
                                    <input type="email"
                                           x-model="supportEmail"
                                           dir="ltr"
                                           placeholder="support@yourcompany.ir"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono transition-all">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                        {{ __('resellerhub::messages.domain_branding.footer_text_label') }}
                                    </label>
                                    <input type="text"
                                           x-model="brandFooterText"
                                           placeholder="{{ __('resellerhub::messages.domain_branding.footer_text_placeholder') }}"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div
                                class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <button type="submit"
                                        :disabled="brandingSubmitting"
                                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 cursor-pointer disabled:opacity-50 inline-flex items-center justify-center gap-2 transition-all">
                                    <template x-if="brandingSubmitting">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="brandingSubmitting ? savingText : saveBrandingBtnText"></span>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Locked White-Label State --}}
                        <div
                            class="p-8 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 mx-auto flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="max-w-md mx-auto space-y-1.5">
                                <h4 class="text-sm font-black text-gray-900 dark:text-white">{{ __('resellerhub::messages.domain_branding.whitelabel_locked_title') }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                    {{ __('resellerhub::messages.domain_branding.whitelabel_locked_desc') }}
                                </p>
                            </div>
                            <div class="pt-2">
                                <button type="button"
                                        @click="activeTab = 'subscription'"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-600/20 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span>{{ __('resellerhub::messages.domain_branding.view_packages_btn') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <script>
        function tenantDomainBrandingHandler() {
            return {
                customDomainInput: @json($currentCustomDomain),
                brandTitle: @json($brandTitle),
                brandPrimaryColor: @json($brandPrimaryColor),
                brandFooterText: @json($brandFooterText),
                supportPhone: @json($supportPhone),
                supportEmail: @json($supportEmail),
                logoPreviewUrl: @json($brandLogoUrl),
                faviconPreviewUrl: @json($brandFaviconUrl),
                logoFile: null,
                faviconFile: null,
                removeLogoFlag: false,
                removeFaviconFlag: false,
                domainSubmitting: false,
                brandingSubmitting: false,
                colorPresets: ['#4f46e5', '#7c3aed', '#059669', '#d97706', '#e11d48', '#0891b2', '#0f172a'],

                checkingAndSavingText: @json(__('resellerhub::messages.domain_branding.checking_and_saving')),
                connectDomainBtnText: @json(__('resellerhub::messages.domain_branding.connect_domain_btn')),
                disconnectConfirmText: @json(__('resellerhub::messages.domain_branding.disconnect_confirm')),
                domainSaveErrorText: @json(__('resellerhub::messages.domain_branding.domain_save_error')),
                serverConnectionErrorText: @json(__('resellerhub::messages.common.server_connection_error')),
                savingText: @json(__('resellerhub::messages.domain_branding.saving')),
                saveBrandingBtnText: @json(__('resellerhub::messages.domain_branding.save_branding_btn')),
                brandingSaveErrorText: @json(__('resellerhub::messages.domain_branding.branding_save_error')),

                handleLogoChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.logoFile = file;
                    this.removeLogoFlag = false;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.logoPreviewUrl = event.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                removeLogo() {
                    this.logoFile = null;
                    this.logoPreviewUrl = null;
                    this.removeLogoFlag = true;
                },

                handleFaviconChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.faviconFile = file;
                    this.removeFaviconFlag = false;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.faviconPreviewUrl = event.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                removeFavicon() {
                    this.faviconFile = null;
                    this.faviconPreviewUrl = null;
                    this.removeFaviconFlag = true;
                },

                async saveCustomDomain() {
                    if (this.domainSubmitting) return;
                    this.domainSubmitting = true;

                    try {
                        const res = await fetch("{{ route('tenant.domain.update') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                custom_domain: this.customDomainInput
                            })
                        });

                        const data = await res.json();
                        this.domainSubmitting = false;

                        if (res.ok && data.success) {
                            if (window.showToast) {
                                window.showToast('success', data.message);
                            }
                            this.customDomainInput = data.custom_domain || '';
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            const err = data.message || this.domainSaveErrorText;
                            if (window.showToast) {
                                window.showToast('error', err);
                            }
                        }
                    } catch (err) {
                        this.domainSubmitting = false;
                        if (window.showToast) {
                            window.showToast('error', this.serverConnectionErrorText);
                        }
                    }
                },

                async disconnectCustomDomain() {
                    if (!confirm(this.disconnectConfirmText)) {
                        return;
                    }
                    this.customDomainInput = '';
                    await this.saveCustomDomain();
                },

                async saveBranding() {
                    if (this.brandingSubmitting) return;
                    this.brandingSubmitting = true;

                    try {
                        const formData = new FormData();
                        formData.append('brand_title', this.brandTitle || '');
                        formData.append('brand_primary_color', this.brandPrimaryColor || '#4f46e5');
                        formData.append('brand_footer_text', this.brandFooterText || '');
                        formData.append('support_phone', this.supportPhone || '');
                        formData.append('support_email', this.supportEmail || '');

                        if (this.removeLogoFlag) {
                            formData.append('remove_brand_logo', '1');
                        } else if (this.logoFile) {
                            formData.append('brand_logo', this.logoFile);
                        }

                        if (this.removeFaviconFlag) {
                            formData.append('remove_brand_favicon', '1');
                        } else if (this.faviconFile) {
                            formData.append('brand_favicon', this.faviconFile);
                        }

                        const res = await fetch("{{ route('tenant.branding.update') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        this.brandingSubmitting = false;

                        if (res.ok && data.success) {
                            if (window.showToast) {
                                window.showToast('success', data.message);
                            }
                            if (data.brand_logo_url) {
                                this.logoPreviewUrl = data.brand_logo_url;
                            }
                            if (data.brand_favicon_url) {
                                this.faviconPreviewUrl = data.brand_favicon_url;
                            }
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            const err = data.message || this.brandingSaveErrorText;
                            if (window.showToast) {
                                window.showToast('error', err);
                            }
                        }
                    } catch (err) {
                        this.brandingSubmitting = false;
                        if (window.showToast) {
                            window.showToast('error', this.serverConnectionErrorText);
                        }
                    }
                }
            };
        }
    </script>
@endif
