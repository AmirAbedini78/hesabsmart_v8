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
use Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished;
use Modules\WebForms\Http\Controllers\WebFormController;

Route::withoutMiddleware([
    PreventRequestsWhenMigrationNeeded::class,
    PreventRequestsWhenUpdateNotFinished::class,
])->group(function () {
    Route::get('/forms/f/{uuid}', [WebFormController::class, 'show'])->name('webform.view');
    Route::post('/forms/f/{uuid}', [WebFormController::class, 'store'])->name('webform.process');
});
