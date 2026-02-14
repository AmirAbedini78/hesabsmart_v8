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
use Modules\Translator\Http\Controllers\Api\TranslationController;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('translation')->group(function () {
        Route::post('/', [TranslationController::class, 'store']);
        Route::get('/{locale}', [TranslationController::class, 'index']);
        Route::put('/{locale}/{group}', [TranslationController::class, 'update']);
    });
});
