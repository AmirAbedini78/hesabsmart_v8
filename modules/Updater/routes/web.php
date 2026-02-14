<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

use Illuminate\Support\Facades\Route;
use Modules\Updater\Http\Controllers\FilePermissionsError;
use Modules\Updater\Http\Controllers\FinalizeUpdateController;
use Modules\Updater\Http\Controllers\MigrateController;
use Modules\Updater\Http\Controllers\UpdateDownloadController;
use Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished;

Route::withoutMiddleware(PreventRequestsWhenUpdateNotFinished::class)->group(function () {
    Route::get('/update/finalize', [FinalizeUpdateController::class, 'show']);
    Route::post('/update/finalize', [FinalizeUpdateController::class, 'finalize']);
});

Route::withoutMiddleware([
    PreventRequestsWhenMigrationNeeded::class,
    PreventRequestsWhenUpdateNotFinished::class,
])->group(function () {
    Route::get('/migrate', [MigrateController::class, 'show']);
    Route::post('/migrate', [MigrateController::class, 'migrate']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/update/errors/permissions', FilePermissionsError::class);

    Route::get('/patches/{token}/{purchase_key?}', [UpdateDownloadController::class, 'downloadPatch']);
});
