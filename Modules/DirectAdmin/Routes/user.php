<?php

use Illuminate\Support\Facades\Route;
use Modules\DirectAdmin\Http\Controllers\User\ServerController;
use Modules\DirectAdmin\Http\Controllers\User\DashboardController;
use Modules\DirectAdmin\Http\Controllers\User\AccountController;
use Modules\DirectAdmin\Http\Controllers\User\AccountDetailController;
use Modules\DirectAdmin\Http\Controllers\User\WordPressController;
use Modules\DirectAdmin\Http\Controllers\User\SettingController;

Route::prefix('directadmin')
    ->name('directadmin.')
    ->group(function () {
        
        // 1. Servers (Server Connection Management)
        Route::get('/servers', [ServerController::class, 'index'])
            ->name('servers.index')
            ->middleware('can:directadmin.settings.manage');

        Route::get('/servers/create', [ServerController::class, 'index'])
            ->name('servers.create')
            ->middleware('can:directadmin.settings.manage');
            
        Route::post('/servers', [ServerController::class, 'store'])
            ->name('servers.store')
            ->middleware('can:directadmin.settings.manage');
            
        Route::put('/servers/{server}', [ServerController::class, 'update'])
            ->name('servers.update')
            ->middleware('can:directadmin.settings.manage');
            
        Route::delete('/servers/{server}', [ServerController::class, 'destroy'])
            ->name('servers.destroy')
            ->middleware('can:directadmin.settings.manage');
            
        Route::post('/servers/test-connection', [ServerController::class, 'testConnection'])
            ->name('servers.test')
            ->middleware('can:directadmin.settings.manage');

        Route::post('/servers/{server}/set-default', [ServerController::class, 'setDefault'])
            ->name('servers.set-default')
            ->middleware('can:directadmin.settings.manage');

        // 2. Server Dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:directadmin.view');

        // 3. Accounts Management
        Route::get('/accounts', [AccountController::class, 'index'])
            ->name('accounts.index')
            ->middleware('can:directadmin.accounts.view');
            
        Route::get('/accounts/create', [AccountController::class, 'create'])
            ->name('accounts.create')
            ->middleware('can:directadmin.accounts.create');
            
        Route::post('/accounts', [AccountController::class, 'store'])
            ->name('accounts.store')
            ->middleware('can:directadmin.accounts.create');
            
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])
            ->name('accounts.destroy')
            ->middleware('can:directadmin.accounts.delete');
            
        Route::post('/accounts/{account}/toggle-suspension', [AccountController::class, 'toggleSuspension'])
            ->name('accounts.toggle-suspension')
            ->middleware('can:directadmin.accounts.suspend');
            
        Route::post('/accounts/sync', [AccountController::class, 'sync'])
            ->name('accounts.sync')
            ->middleware('can:directadmin.accounts.sync');

        // 4. Account Details & Password Change
        Route::get('/accounts/{username}/show', [AccountDetailController::class, 'show'])
            ->name('accounts.show')
            ->middleware('can:directadmin.accounts.view');
            
        Route::post('/accounts/{account}/update-client', [AccountController::class, 'updateClient'])
            ->name('accounts.update-client')
            ->middleware('can:directadmin.accounts.view');

        Route::post('/accounts/{account}/update-order', [AccountController::class, 'updateOrder'])
            ->name('accounts.update-order')
            ->middleware('can:directadmin.accounts.view');

        Route::post('/accounts/link-order', [AccountController::class, 'linkOrderAccount'])
            ->name('accounts.link-order')
            ->middleware('can:directadmin.accounts.view');

        Route::post('/accounts/unlink-order', [AccountController::class, 'unlinkOrderAccount'])
            ->name('accounts.unlink-order')
            ->middleware('can:directadmin.accounts.view');
            
        Route::post('/accounts/{username}/change-password', [AccountDetailController::class, 'changePassword'])
            ->name('accounts.change-password')
            ->middleware('can:directadmin.accounts.password');

        Route::post('/accounts/{username}/modify', [AccountDetailController::class, 'modify'])
            ->name('accounts.modify')
            ->middleware('can:directadmin.accounts.view');

        // 5. Login History
        Route::get('/accounts/{username}/login-history', [AccountDetailController::class, 'loginHistory'])
            ->name('accounts.login-history')
            ->middleware('can:directadmin.login-history.view');

        // 6. DirectAdmin Module Settings
        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index')
            ->middleware('can:directadmin.settings.manage');

        Route::post('/settings', [SettingController::class, 'update'])
            ->name('settings.update')
            ->middleware('can:directadmin.settings.manage');

        Route::post('/settings/seed-category', [SettingController::class, 'seedCategory'])
            ->name('settings.seed-category')
            ->middleware('can:directadmin.settings.manage');
    });
