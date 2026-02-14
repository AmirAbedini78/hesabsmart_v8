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
use Modules\Documents\Http\Controllers\DocumentController;
use Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished;

Route::withoutMiddleware([
    PreventRequestsWhenMigrationNeeded::class,
    PreventRequestsWhenUpdateNotFinished::class,
])->group(function () {
    Route::get('/d/{uuid}', [DocumentController::class, 'show'])->name('document.public');
    Route::get('/d/{uuid}/pdf', [DocumentController::class, 'pdf'])->name('document.pdf');
});
