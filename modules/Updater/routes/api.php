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
use Modules\Updater\Http\Controllers\Api\PatchController;
use Modules\Updater\Http\Controllers\Api\UpdateController;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/patches', [PatchController::class, 'index']);
    Route::post('/patches/{token?}', [PatchController::class, 'apply']);
    Route::get('/update', [UpdateController::class, 'index']);
    Route::post('/update', [UpdateController::class, 'update']);
});
