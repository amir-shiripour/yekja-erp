<?php

use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Http\Controllers\ResellerHubController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('resellerhub', ResellerHubController::class)->names('resellerhub');
});
