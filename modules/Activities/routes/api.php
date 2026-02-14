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
use Modules\Activities\Http\Controllers\Api\ActivityStateController;
use Modules\Activities\Http\Controllers\Api\CalendarOAuthController;

Route::middleware('auth:sanctum')->group(function () {
    /**
     * @deprecated Use regular activity update with "is_completed" attribute.
     */
    Route::post('/activities/{activity}/complete', [ActivityStateController::class, 'complete']);
    Route::post('/activities/{activity}/incomplete', [ActivityStateController::class, 'incomplete']);

    // Calendar routes
    Route::prefix('calendar')->group(function () {
        Route::get('/account', [CalendarOAuthController::class, 'index']);
        Route::post('/account', [CalendarOAuthController::class, 'save']);
        Route::delete('/account', [CalendarOAuthController::class, 'destroy']);
    });

    Route::get('/calendars/{account}', [CalendarOAuthController::class, 'calendars']);
});
