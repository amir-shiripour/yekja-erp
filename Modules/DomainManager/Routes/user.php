<?php

use Illuminate\Support\Facades\Route;
use Modules\DomainManager\Http\Controllers\User\ConnectionController;
use Modules\DomainManager\Http\Controllers\User\DashboardController;
use Modules\DomainManager\Http\Controllers\User\SearchController;
use Modules\DomainManager\Http\Controllers\User\DomainController;

Route::prefix('domainmanager')
    ->name('domainmanager.')
    ->group(function () {
        
        // 1. Reseller Connections CRUD
        Route::get('/connections', [ConnectionController::class, 'index'])
            ->name('connections.index')
            ->middleware('can:domainmanager.connections.manage');

        Route::get('/connections/create', [ConnectionController::class, 'index'])
            ->name('connections.create')
            ->middleware('can:domainmanager.connections.manage');
            
        Route::post('/connections', [ConnectionController::class, 'store'])
            ->name('connections.store')
            ->middleware('can:domainmanager.connections.manage');
            
        Route::put('/connections/{reseller}', [ConnectionController::class, 'update'])
            ->name('connections.update')
            ->middleware('can:domainmanager.connections.manage');
            
        Route::delete('/connections/{reseller}', [ConnectionController::class, 'destroy'])
            ->name('connections.destroy')
            ->middleware('can:domainmanager.connections.manage');
            
        Route::post('/connections/test-connection', [ConnectionController::class, 'testConnection'])
            ->name('connections.test')
            ->middleware('can:domainmanager.connections.manage');

        // 2. Dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:domainmanager.view');

        // 3. Search & Registrations
        Route::get('/search', [SearchController::class, 'index'])
            ->name('search.index')
            ->middleware('can:domainmanager.domains.register');
            
        Route::post('/search/check', [SearchController::class, 'check'])
            ->name('search.check')
            ->middleware('can:domainmanager.domains.register');

        Route::post('/search/check-ir', [SearchController::class, 'checkIrDomain'])
            ->name('search.check-ir')
            ->middleware('can:domainmanager.domains.register');

        Route::get('/register', [SearchController::class, 'showRegisterForm'])
            ->name('register.show')
            ->middleware('can:domainmanager.domains.register');
            
        Route::post('/register', [SearchController::class, 'register'])
            ->name('register.submit')
            ->middleware('can:domainmanager.domains.register');

        Route::post('/transfer', [SearchController::class, 'transfer'])
            ->name('transfer.submit')
            ->middleware('can:domainmanager.domains.register');

        // 4. Domains Management
        Route::get('/domains', [DomainController::class, 'index'])
            ->name('domains.index')
            ->middleware('can:domainmanager.domains.view');

        Route::get('/domains/{domainName}/show', [DomainController::class, 'show'])
            ->name('domains.show')
            ->middleware('can:domainmanager.domains.view');
            
        Route::post('/domains/sync', [DomainController::class, 'sync'])
            ->name('domains.sync')
            ->middleware('can:domainmanager.domains.view');

        Route::post('/domains/{domainName}/update-client', [DomainController::class, 'updateClient'])
            ->name('domains.update-client')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/update-order', [DomainController::class, 'updateOrder'])
            ->name('domains.update-order')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/sync-all', [DomainController::class, 'syncAll'])
            ->name('domains.sync-all')
            ->middleware('can:domainmanager.domains.view');

        Route::post('/domains/{domainName}/modify-ns', [DomainController::class, 'modifyNS'])
            ->name('domains.modify-ns')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/toggle-privacy', [DomainController::class, 'togglePrivacy'])
            ->name('domains.toggle-privacy')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/toggle-lock', [DomainController::class, 'toggleLock'])
            ->name('domains.toggle-lock')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/renew', [DomainController::class, 'renew'])
            ->name('domains.renew')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/fetch-epp', [DomainController::class, 'fetchEpp'])
            ->name('domains.fetch-epp')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/modify-contacts', [DomainController::class, 'modifyContacts'])
            ->name('domains.modify-contacts')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/child-hosts/add', [DomainController::class, 'addChildHost'])
            ->name('domains.child-hosts.add')
            ->middleware('can:domainmanager.domains.manage');

        Route::delete('/domains/{domainName}/child-hosts/delete', [DomainController::class, 'deleteChildHost'])
            ->name('domains.child-hosts.delete')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/domains/{domainName}/dnssec/add', [DomainController::class, 'addDnsSec'])
            ->name('domains.dnssec.add')
            ->middleware('can:domainmanager.domains.manage');

        Route::delete('/domains/{domainName}/dnssec/delete', [DomainController::class, 'deleteDnsSec'])
            ->name('domains.dnssec.delete')
            ->middleware('can:domainmanager.domains.manage');

        // 5. Additional Views & Settings
        Route::get('/prices', [DomainController::class, 'prices'])
            ->name('prices.index')
            ->middleware('can:domainmanager.domains.view');

        Route::get('/logs', [DomainController::class, 'logs'])
            ->name('logs.index')
            ->middleware('can:domainmanager.view');

        Route::get('/transactions', [DomainController::class, 'transactions'])
            ->name('transactions.index')
            ->middleware('can:domainmanager.view');

        Route::get('/settings', [\Modules\DomainManager\Http\Controllers\User\SettingController::class, 'index'])
            ->name('settings.index')
            ->middleware('can:domainmanager.view');

        Route::post('/settings/seed-category', [\Modules\DomainManager\Http\Controllers\User\SettingController::class, 'seedCategory'])
            ->name('settings.seed-category')
            ->middleware('can:domainmanager.view');

        // 6. Order Domain Provisioning & Linkage
        Route::post('/orders/store-domain', [DomainController::class, 'storeOrderDomain'])
            ->name('orders.store-domain')
            ->middleware('can:domainmanager.domains.register');

        Route::post('/orders/link-domain', [DomainController::class, 'linkOrderDomain'])
            ->name('orders.link-domain')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/orders/unlink-domain', [DomainController::class, 'unlinkOrderDomain'])
            ->name('orders.unlink-domain')
            ->middleware('can:domainmanager.domains.manage');

        Route::post('/orders/check-availability', [DomainController::class, 'checkOrderDomainAvailability'])
            ->name('orders.check-availability')
            ->middleware('can:domainmanager.domains.register');
    });
