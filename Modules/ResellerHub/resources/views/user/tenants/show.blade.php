@php
    use App\Tenancy\TenantManager;
    use Modules\ResellerHub\App\Helpers\ResellerHelper;
    use Modules\ResellerHub\App\Models\ResellerInvoice;
    use Modules\ResellerHub\App\Services\TenantQuotaService;

    $tenantManager = app(TenantManager::class);
    $tenantUrl = $tenantManager->getTenantUrl($tenant, 'user/dashboard');
    $displayUrl = $tenantManager->getTenantDisplayUrl($tenant);

    $tenantBranding = is_array($tenant->branding) ? $tenant->branding : [];
    $isCustomBranding = !empty($tenantBranding['is_custom']);
    $brandTitle = $tenantBranding['brand_title'] ?? ($tenant->name ?? '');
    $brandPrimaryColor = $tenantBranding['brand_primary_color'] ?? '#4f46e5';
    $brandLogoUrl = !empty($tenantBranding['brand_logo']) ? \Illuminate\Support\Facades\Storage::url($tenantBranding['brand_logo']) : null;
    $brandFaviconUrl = !empty($tenantBranding['brand_favicon']) ? \Illuminate\Support\Facades\Storage::url($tenantBranding['brand_favicon']) : null;
    $brandFooterText = $tenantBranding['brand_footer_text'] ?? '';
    $supportPhone = $tenantBranding['support_phone'] ?? '';
    $supportEmail = $tenantBranding['support_email'] ?? '';

    $tenantInvoices = $tenantInvoices ?? ResellerInvoice::where('tenant_id', $tenant->id)->latest('issue_date')->limit(5)->get();
    $currencyLabel = $currencyLabel ?? reseller_currency_label($resellerSettings['currency'] ?? 'toman');
    $availablePeriods = $availablePeriods ?? ResellerHelper::getTenantRenewalPeriods($tenant);
    $tenantPackage = $tenant->package;

    $subAccount = $tenant->subAccount;
    $managerName = $subAccount?->name ?? $subAccount?->user?->name ?? '—';
    $managerMobile = $subAccount?->mobile ?? $subAccount?->user?->mobile ?? null;
    $managerEmail = $subAccount?->email ?? $subAccount?->user?->email ?? '—';
    $parentReseller = $tenant->reseller;
    $quotasSummary = $quotasSummary ?? app(TenantQuotaService::class)->getQuotasSummary($tenant);

    $activeModulesCount = $tenant->modules->where('is_active', true)->count();
    if ($activeModulesCount === 0 && $tenant->package && is_array($tenant->package->modules)) {
        $activeModulesCount = count($tenant->package->modules);
    }
@endphp
@extends('layouts.user')

@section('title', 'جزییات سامانه ' . $tenant->name)

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')

    <div x-data="tenantDetails(window.tenantPageConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 relative">

        @include('resellerhub::partials.toast')

        {{-- Header & Quick Actions --}}
        @include('resellerhub::user.tenants.partials.show.header')

        {{-- Tab Navigation Bar --}}
        <div
            class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-100 dark:border-gray-700/60 p-1.5 shadow-xs backdrop-blur-xl">
            <nav class="flex items-center gap-1.5 overflow-x-auto scrollbar-none" aria-label="Tabs">
                {{-- Tab 1: Overview & Subscription --}}
                <button type="button"
                        @click="setTab('overview')"
                        :class="activeTab === 'overview'
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>نمای کلی و اشتراک</span>
                </button>

                {{-- Tab 2: Modules & Capabilities --}}
                <button type="button"
                        @click="setTab('modules')"
                        :class="activeTab === 'modules'
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>ماژول‌ها و امکانات</span>
                    <span
                        :class="activeTab === 'modules' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                        class="px-2 py-0.5 rounded-full text-xs font-bold">
                        {{ reseller_fa_num($activeModulesCount) }}
                    </span>
                </button>

                {{-- Tab 3: Resource Quotas & Usages --}}
                <button type="button"
                        @click="setTab('quotas')"
                        :class="activeTab === 'quotas'
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>سهمیه‌ها و مصرف منابع</span>
                </button>

                {{-- Tab 4: White-Label Branding --}}
                <button type="button"
                        @click="setTab('branding')"
                        :class="activeTab === 'branding'
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <span>برندینگ و تم (White-Label)</span>
                </button>

                {{-- Tab 5: Invoices & Financial --}}
                <button type="button"
                        @click="setTab('invoices')"
                        :class="activeTab === 'invoices'
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                    <span>سوابق مالی و فاکتورها</span>
                    <span
                        :class="activeTab === 'invoices' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                        class="px-2 py-0.5 rounded-full text-xs font-bold">
                        {{ reseller_fa_num($tenantInvoices->count()) }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- Tab 1 Content: Overview & Subscription --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                @include('resellerhub::user.tenants.partials.show.subscription-card')
                @include('resellerhub::user.tenants.partials.show.manager-card')
            </div>
        </div>

        {{-- Tab 2 Content: Modules & Capabilities --}}
        <div x-show="activeTab === 'modules'" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6" style="display: none;">
            @include('resellerhub::user.tenants.partials.show.modules-card')
        </div>

        {{-- Tab 3 Content: Resource Quotas & Usages --}}
        <div x-show="activeTab === 'quotas'" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6" style="display: none;">
            @include('resellerhub::user.tenants.partials.show.quotas-card')
        </div>

        {{-- Tab 4 Content: White-Label Branding --}}
        <div x-show="activeTab === 'branding'" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6" style="display: none;">
            @include('resellerhub::user.tenants.partials.show.branding-card')
        </div>

        {{-- Tab 5 Content: Invoices & Financial --}}
        <div x-show="activeTab === 'invoices'" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6" style="display: none;">
            @include('resellerhub::user.tenants.partials.show.invoices-card')
        </div>

        {{-- Real-time Suspend/Unsuspend Modal --}}
        @include('resellerhub::partials.suspend-modal')

        {{-- Real-time License Renewal Modal --}}
        @include('resellerhub::partials.renew-modal')

        {{-- Custom Domain Management Modal --}}
        @include('resellerhub::partials.custom-domain-modal')

        {{-- Custom Domain DNS Instructions Modal --}}
        @include('resellerhub::user.tenants.partials.show.dns-guide-modal')

        {{-- Hard Delete Tenant Modal --}}
        @include('resellerhub::partials.tenant-delete-modal')
    </div>

    @push('scripts')
        @include('resellerhub::user.tenants.partials.show.show-script')
    @endpush
@endsection
