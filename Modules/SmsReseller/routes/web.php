<?php

use Illuminate\Support\Facades\Route;
use Modules\SmsReseller\App\Http\Controllers\SmsResellerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['web', 'auth'])->prefix('user/smsreseller')->name('user.smsreseller.')->group(function () {
    Route::get('/', [SmsResellerController::class, 'index'])
        ->middleware('permission:smsreseller.view|smsreseller.manage')
        ->name('index');

    Route::get('/user-credits', [SmsResellerController::class, 'userCredits'])
        ->middleware('permission:smsreseller.user-credits.view|smsreseller.manage')
        ->name('user_credits');

    Route::get('/user-payments', [SmsResellerController::class, 'userPayments'])
        ->middleware('permission:smsreseller.user-payments.view|smsreseller.manage')
        ->name('user_payments');

    Route::get('/panel-payments', [SmsResellerController::class, 'panelPayments'])
        ->middleware('permission:smsreseller.panel-payments.view|smsreseller.manage')
        ->name('panel_payments');

    Route::post('/sync', [SmsResellerController::class, 'syncData'])
        ->middleware('permission:smsreseller.sync|smsreseller.manage')
        ->name('sync');

    Route::post('/record-document', [SmsResellerController::class, 'recordAccountingDocument'])
        ->middleware('permission:smsreseller.accounting.record|smsreseller.manage')
        ->name('record_document');

    Route::post('/record-panel-expense-document', [SmsResellerController::class, 'recordPanelExpenseDocument'])
        ->middleware('permission:smsreseller.accounting.record|smsreseller.manage')
        ->name('record_panel_expense_document');

    Route::post('/record-user-income-document', [SmsResellerController::class, 'recordUserIncomeDocument'])
        ->middleware('permission:smsreseller.accounting.record|smsreseller.manage')
        ->name('record_user_income_document');

    Route::get('/user-profitability', [SmsResellerController::class, 'userProfitability'])
        ->middleware('permission:smsreseller.profitability.view|smsreseller.manage')
        ->name('user_profitability');

    Route::get('/user-profitability/{username}', [SmsResellerController::class, 'userProfitabilityShow'])
        ->middleware('permission:smsreseller.profitability.view|smsreseller.manage')
        ->name('user_profitability.show');

    Route::get('/margin-calculator', [SmsResellerController::class, 'marginCalculator'])
        ->middleware('permission:smsreseller.margins.view|smsreseller.manage')
        ->name('margin_calculator');

    Route::post('/margin-calculator/save', [SmsResellerController::class, 'saveMarginPrices'])
        ->middleware('permission:smsreseller.margins.manage|smsreseller.manage')
        ->name('save_margin_prices');

    Route::get('/reconciliation', [SmsResellerController::class, 'reconciliation'])
        ->middleware('permission:smsreseller.reconciliation.view|smsreseller.manage')
        ->name('reconciliation');

    Route::post('/reconciliation/cumulative', [SmsResellerController::class, 'recordCumulativeReconciliation'])
        ->middleware('permission:smsreseller.reconciliation.manage|smsreseller.manage')
        ->name('reconciliation.cumulative');

    Route::get('/settings', [SmsResellerController::class, 'settings'])
        ->middleware('permission:smsreseller.settings.manage|smsreseller.manage')
        ->name('settings');

    Route::get('/config', [SmsResellerController::class, 'settings'])
        ->middleware('permission:smsreseller.settings.manage|smsreseller.manage')
        ->name('config');

    Route::post('/settings', [SmsResellerController::class, 'saveSettings'])
        ->middleware('permission:smsreseller.settings.manage|smsreseller.manage')
        ->name('settings.save');
});
