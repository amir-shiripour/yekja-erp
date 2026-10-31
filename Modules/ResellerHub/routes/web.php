<?php

use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Http\Controllers\PublicPricingController;
use Modules\ResellerHub\App\Http\Controllers\ResellerDashboardController;
use Modules\ResellerHub\App\Http\Controllers\ResellerInvoiceController;
use Modules\ResellerHub\App\Http\Controllers\ResellerOrderController;
use Modules\ResellerHub\App\Http\Controllers\ResellerPackageController;
use Modules\ResellerHub\App\Http\Controllers\ResellerPricingController;
use Modules\ResellerHub\App\Http\Controllers\TenantProvisioningController;
use Modules\ResellerHub\App\Http\Controllers\TenantDataExportController;
use Modules\ResellerHub\App\Http\Controllers\ResellerBrandingController;
use Modules\ResellerHub\App\Http\Controllers\ResellerSettingController;
use Modules\ResellerHub\App\Http\Controllers\ResellerSubAccountController;
use Modules\ResellerHub\App\Http\Controllers\TenantSsoController;
use Modules\ResellerHub\App\Http\Controllers\TenantSubscriptionController;
use Modules\ResellerHub\App\Http\Controllers\TenantDomainBrandingController;
use Modules\ResellerHub\App\Http\Controllers\SplashController;
use Modules\ResellerHub\App\Http\Controllers\PortalCheckoutController;
use Modules\ResellerHub\App\Livewire\Portal\TenantProvisioningLoader;
use Modules\ResellerHub\App\Livewire\Portal\Checkout;

/*
|--------------------------------------------------------------------------
| ResellerHub Web Routes
|--------------------------------------------------------------------------
*/

// Public routes for Customer Acquisition & Gateway Callbacks
Route::middleware(['web'])->group(function () {
    Route::get('/resellerhub/pricing', [PublicPricingController::class, 'index'])->name('resellerhub.pricing');

    // Portal for Customer Acquisition / Registration
    Route::view('/get-started', 'resellerhub::portal.index')->name('portal.get-started');

    // Bank gateway callback - accessible publicly by banks without auth requirement
    Route::match(['get', 'post'], '/get-started/checkout/verify/{invoice?}/{gateway?}', [PortalCheckoutController::class, 'verify'])
        ->middleware('throttle:20,1')
        ->name('portal.checkout.verify');

    Route::middleware(['auth'])->group(function () {
        Route::get('/get-started/checkout', Checkout::class)->name('portal.checkout.index');
        Route::get('/get-started/loading', TenantProvisioningLoader::class)->name('portal.onboarding.loading');
        Route::get('/get-started/pay/{invoice}', [PortalCheckoutController::class, 'payInvoice'])->name('portal.invoices.pay');
        Route::post('/get-started/pay/{invoice}', [PortalCheckoutController::class, 'processInvoicePayment'])->name('portal.invoices.pay.process');
    });

    // Online gateway payment callback - accessible without auth so bank POST/GET callbacks are never blocked
    Route::match(['get', 'post'], '/reseller/invoices/{invoice}/verify/{gateway?}', [ResellerInvoiceController::class, 'verifyOnlinePayment'])
        ->middleware('throttle:10,1')
        ->name('reseller.invoices.verify');
});

Route::middleware(['web', 'auth', 'role_or_permission:super-admin|resellerhub.view'])->prefix('reseller')->name('reseller.')->group(function () {
    Route::get('/', [ResellerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/switch-context', [ResellerDashboardController::class, 'switchContext'])->name('switch-context');

    // Tenants Management
    Route::get('/tenants', [TenantProvisioningController::class, 'index'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.view')
        ->name('tenants.index');

    Route::middleware('role_or_permission:super-admin|resellerhub.tenants.create')->group(function () {
        Route::get('/tenants/create', [TenantProvisioningController::class, 'create'])->name('tenants.create');
        Route::match(['get', 'post'], '/tenants/check-subdomain', [TenantProvisioningController::class, 'checkSubdomain'])
            ->middleware('throttle:30,1')
            ->name('tenants.check-subdomain');
        Route::match(['get', 'post'], '/tenants/check-domain', [TenantProvisioningController::class, 'checkDomain'])
            ->middleware('throttle:30,1')
            ->name('tenants.check-domain');
        Route::post('/tenants', [TenantProvisioningController::class, 'store'])->name('tenants.store');
    });

    Route::get('/tenants/{tenant}', [ResellerDashboardController::class, 'showTenant'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.view')
        ->name('tenants.show');

    Route::match(['GET', 'POST'], '/tenants/{tenant}/login-as', [TenantProvisioningController::class, 'loginAs'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.login-as')
        ->name('tenants.login-as');

    Route::middleware('role_or_permission:super-admin|resellerhub.tenants.edit')->group(function () {
        Route::get('/tenants/{tenant}/edit', [TenantProvisioningController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantProvisioningController::class, 'update'])->name('tenants.update');
    });

    Route::post('/tenants/{tenant}/toggle-status', [ResellerDashboardController::class, 'toggleStatus'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.toggle-status')
        ->name('tenants.toggle-status');

    Route::post('/tenants/{tenant}/renew', [ResellerDashboardController::class, 'renewTenant'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.renew')
        ->name('tenants.renew');

    Route::post('/tenants/{tenant}/update-domain', [ResellerDashboardController::class, 'updateDomain'])
        ->middleware('role_or_permission:super-admin|resellerhub.tenants.domain')
        ->name('tenants.update-domain');

    // Tenant Database & SQL Data Export
    Route::middleware('role_or_permission:super-admin|resellerhub.tenants.export')->group(function () {
        Route::get('/tenants-export', [TenantDataExportController::class, 'index'])->name('tenants.export');
        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/tenants/{tenant}/export-sql', [TenantDataExportController::class, 'exportSql'])
                ->middleware('role_or_permission:super-admin|resellerhub.tenants.export_sql')
                ->name('tenants.export-sql');
            Route::get('/tenants/{tenant}/export-json', [TenantDataExportController::class, 'exportJson'])->name('tenants.export-json');
            Route::post('/tenants/{tenant}/export-queue', [TenantDataExportController::class, 'queueExport'])->name('tenants.export-queue');
        });
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.branding|resellerhub.settings.manage')->group(function () {
        Route::get('/branding', [ResellerBrandingController::class, 'edit'])->name('branding.edit');
        Route::post('/branding', [ResellerBrandingController::class, 'update'])->name('branding.update');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.settings.manage')->group(function () {
        Route::get('/settings', [ResellerSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [ResellerSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/seed-workflows', [ResellerSettingController::class, 'seedWorkflows'])->name('settings.seed-workflows');
        Route::post('/settings/tenancy/sync', [ResellerSettingController::class, 'syncTenancy'])->name('settings.tenancy.sync');
        Route::post('/settings/tenancy/add-model', [ResellerSettingController::class, 'addTenancyModel'])->name('settings.tenancy.add-model');
        Route::post('/settings/tenancy/remove-model', [ResellerSettingController::class, 'removeTenancyModel'])->name('settings.tenancy.remove-model');
    });

    // Sub-accounts management
    Route::middleware('role_or_permission:super-admin|resellerhub.sub-accounts.create')->group(function () {
        Route::get('/sub-accounts/create', [ResellerSubAccountController::class, 'create'])->name('sub-accounts.create');
        Route::post('/sub-accounts', [ResellerSubAccountController::class, 'store'])->name('sub-accounts.store');
        Route::post('/sub-accounts/quick-store', [ResellerSubAccountController::class, 'quickStore'])->name('sub-accounts.quick-store');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.sub-accounts.edit')->group(function () {
        Route::get('/sub-accounts/{subAccount}/edit', [ResellerSubAccountController::class, 'edit'])->name('sub-accounts.edit');
        Route::put('/sub-accounts/{subAccount}', [ResellerSubAccountController::class, 'update'])->name('sub-accounts.update');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.sub-accounts.view')->group(function () {
        Route::get('/sub-accounts/search', [ResellerSubAccountController::class, 'search'])->name('sub-accounts.search');
        Route::get('/sub-accounts', [ResellerSubAccountController::class, 'index'])->name('sub-accounts.index');
        Route::get('/sub-accounts/{subAccount}', [ResellerSubAccountController::class, 'show'])->name('sub-accounts.show');
    });

    Route::post('/sub-accounts/{subAccount}/toggle-status', [ResellerSubAccountController::class, 'toggleStatus'])
        ->middleware('role_or_permission:super-admin|resellerhub.sub-accounts.toggle-status')
        ->name('sub-accounts.toggle-status');

    // Custom Packages management
    Route::middleware('role_or_permission:super-admin|resellerhub.packages.create')->group(function () {
        Route::get('/packages/create', [ResellerPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [ResellerPackageController::class, 'store'])->name('packages.store');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.packages.view')->group(function () {
        Route::get('/packages', [ResellerPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/{package}', [ResellerPackageController::class, 'show'])->name('packages.show')->whereNumber('package');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.packages.edit')->group(function () {
        Route::get('/packages/{package}/edit', [ResellerPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{package}', [ResellerPackageController::class, 'update'])->name('packages.update');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.packages.delete')->group(function () {
        Route::post('/packages/{package}/toggle-status', [ResellerPackageController::class, 'toggleStatus'])->name('packages.toggle-status');
        Route::delete('/packages/{package}', [ResellerPackageController::class, 'destroy'])->name('packages.destroy');
    });

    // Module & Capability Pricing
    Route::middleware('role_or_permission:super-admin|resellerhub.pricings.view')->group(function () {
        Route::get('/pricings', [ResellerPricingController::class, 'index'])->name('pricings.index');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.pricings.manage')->group(function () {
        Route::post('/pricings', [ResellerPricingController::class, 'store'])->name('pricings.store');
        Route::post('/pricings/toggle-itemized', [ResellerPricingController::class, 'toggleItemized'])->name('pricings.toggle-itemized');
    });

    // Invoices & Billing
    Route::middleware('role_or_permission:super-admin|resellerhub.invoices.create')->group(function () {
        Route::get('/invoices/create', [ResellerInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [ResellerInvoiceController::class, 'store'])->name('invoices.store');
        Route::post('/invoices/{invoice}/renew', [ResellerInvoiceController::class, 'renew'])->name('invoices.renew');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.invoices.view')->group(function () {
        Route::get('/invoices', [ResellerInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [ResellerInvoiceController::class, 'show'])->name('invoices.show')->whereNumber('invoice');
    });

    Route::delete('/invoices/{invoice}', [ResellerInvoiceController::class, 'destroy'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.delete')
        ->name('invoices.destroy');

    Route::post('/invoices/{invoice}/cancel', [ResellerInvoiceController::class, 'cancel'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.delete|resellerhub.invoices.create')
        ->name('invoices.cancel');

    Route::post('/invoices/{invoice}/update-status', [ResellerInvoiceController::class, 'updateStatus'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.payments|resellerhub.manage')
        ->name('invoices.update-status');

    Route::post('/invoices/{invoice}/pay', [ResellerInvoiceController::class, 'markAsPaid'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.payments')
        ->name('invoices.pay');

    // Orders Management
    Route::middleware('role_or_permission:super-admin|resellerhub.orders.view')->group(function () {
        Route::get('/orders', [ResellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [ResellerOrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware('role_or_permission:super-admin|resellerhub.orders.manage')->group(function () {
        Route::put('/orders/{order}', [ResellerOrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/renewal-invoice', [ResellerOrderController::class, 'createRenewalInvoice'])->name('orders.renewal-invoice');
        Route::post('/orders/{order}/add-items', [ResellerOrderController::class, 'addItems'])->name('orders.add-items');
        Route::post('/orders/{order}/remove-item', [ResellerOrderController::class, 'removeItem'])->name('orders.remove-item');
    });

    Route::post('/orders/{order}/provision', [ResellerOrderController::class, 'provision'])
        ->middleware('role_or_permission:super-admin|resellerhub.provision|resellerhub.orders.manage')
        ->name('orders.provision');

    Route::post('/orders/{order}/upgrade-package', [ResellerOrderController::class, 'upgradePackage'])
        ->middleware('role_or_permission:super-admin|resellerhub.orders.upgrade|resellerhub.orders.manage')
        ->name('orders.upgrade-package');
});

// Reseller Invoice Payment & Print routes - accessible by authenticated invoice parties (reseller or customer)
Route::middleware(['web', 'auth', 'throttle:30,1'])->prefix('reseller')->name('reseller.')->group(function () {
    Route::get('/invoices/{invoice}/print', [ResellerInvoiceController::class, 'print'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.print|resellerhub.invoices.view')
        ->name('invoices.print');
    Route::get('/invoices/{invoice}/payment', [ResellerInvoiceController::class, 'payment'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.payments|resellerhub.invoices.view')
        ->name('invoices.payment');
    Route::post('/invoices/{invoice}/payment', [ResellerInvoiceController::class, 'storePayment'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.payments')
        ->name('invoices.payment.store');
    Route::post('/invoices/{invoice}/payments/{payment}/cancel', [ResellerInvoiceController::class, 'cancelPayment'])
        ->middleware('role_or_permission:super-admin|resellerhub.invoices.payments')
        ->name('invoices.payments.cancel');
});


$ssoRateLimit = max(1, (int)config('resellerhub.sso.rate_limit', 20));

Route::middleware(['web', 'throttle:' . $ssoRateLimit . ',1'])->prefix('reseller')->group(function () {
    Route::get('/sso-login', [TenantSsoController::class, 'handle'])->name('tenant.sso-login');
    Route::get('/sso-return', [TenantSsoController::class, 'handleReturn'])->name('tenant.sso-return');
    Route::post('/exit-impersonation', [TenantSsoController::class, 'exitImpersonation'])->name('tenant.exit-impersonation');
});

// Tenant Subscription & Reseller Quotas View (accessible by tenant users/admin)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/user/subscription', [TenantSubscriptionController::class, 'show'])->name('tenant.subscription');
    Route::post('/user/subscription/request', [TenantSubscriptionController::class, 'requestUpgrade'])->name('tenant.subscription.request');
    Route::post('/user/subscription/invoices/{invoice}/cancel', [TenantSubscriptionController::class, 'cancelInvoice'])->name('tenant.subscription.invoices.cancel');
    Route::get('/user/quota-check', [TenantSubscriptionController::class, 'checkQuota'])->name('tenant.quota.check');
    Route::post('/user/tenant/domain', [TenantDomainBrandingController::class, 'updateDomain'])->name('tenant.domain.update');
    Route::post('/user/tenant/branding', [TenantDomainBrandingController::class, 'updateBranding'])->name('tenant.branding.update');
});

Route::middleware(['web', 'auth'])->prefix('resellerhub')->group(function () {
    Route::get('/splash/registered', [SplashController::class, 'registered'])->name('splash.registered');
    Route::get('/splash/welcome-back', [SplashController::class, 'welcomeBack'])->name('splash.welcome-back');
});
