@includeIf('resellerhub::user.packages.partials.icons-script')

<div x-data="tenantSubscriptionHandler()" class="space-y-8">
    @include('resellerhub::tenant.partials.subscription.hero-section')
    @include('resellerhub::tenant.partials.subscription.quotas-section')
    @include('resellerhub::tenant.partials.subscription.feature-matrix')
    @include('resellerhub::tenant.partials.subscription.invoices-section')
    @include('resellerhub::tenant.partials.subscription.modals')
    @include('resellerhub::tenant.partials.subscription.scripts')
</div>

@include('resellerhub::partials.notice-modal')