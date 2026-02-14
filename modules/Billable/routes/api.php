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
use Modules\Billable\Http\Controllers\Api\ActiveProductController;
use Modules\Billable\Http\Controllers\Api\BillableController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/{resource}/{resourceId}/billable', [BillableController::class, 'show']);
    Route::post('/{resource}/{resourceId}/billable', [BillableController::class, 'save']);

    Route::get('/{resource}/active', [ActiveProductController::class, 'handle']);
});
