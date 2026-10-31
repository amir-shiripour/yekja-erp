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
@endphp
@extends('layouts.user')

@section('title', 'جزییات سامانه ' . $tenant->name)

@section('content')
    @include('resellerhub::user.packages.partials.icons-script')

    <div x-data="tenantDetails(window.tenantPageConfig)"
         class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 relative">

        @include('resellerhub::partials.toast')

        {{-- Header & Quick Actions --}}
        @include('resellerhub::user.tenants.partials.show.header')

        {{-- Main Specs & Manager Info Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            @include('resellerhub::user.tenants.partials.show.subscription-card')
            @include('resellerhub::user.tenants.partials.show.manager-card')
        </div>

        {{-- Resource Quotas & Live Usages Card --}}
        @include('resellerhub::user.tenants.partials.show.quotas-card')

        {{-- Active Modules & Capabilities Card --}}
        @include('resellerhub::user.tenants.partials.show.modules-card')

        {{-- White-Label Branding Card --}}
        @include('resellerhub::user.tenants.partials.show.branding-card')

        {{-- Recent Invoices Card --}}
        @include('resellerhub::user.tenants.partials.show.invoices-card')

        {{-- Real-time Suspend/Unsuspend Modal --}}
        @include('resellerhub::partials.suspend-modal')

        {{-- Real-time License Renewal Modal --}}
        @include('resellerhub::partials.renew-modal')

        {{-- Custom Domain Management Modal --}}
        @include('resellerhub::partials.custom-domain-modal')

        {{-- Custom Domain DNS Instructions Modal --}}
        @include('resellerhub::user.tenants.partials.show.dns-guide-modal')
    </div>

    @push('scripts')
        @include('resellerhub::user.tenants.partials.show.show-script')
    @endpush
@endsection
