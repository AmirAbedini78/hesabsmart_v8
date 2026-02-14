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
use Modules\Deals\Http\Controllers\Api\DealBoardController;
use Modules\Deals\Http\Controllers\Api\DealStatusController;
use Modules\Deals\Http\Controllers\Api\PipelineStageController;

Route::middleware('auth:sanctum')->group(function () {
    /**
     * @deprecated Use regular deal update with "status" attribute.
     */
    Route::put('/deals/{deal}/status/{status}', [DealStatusController::class, 'handle']);

    Route::prefix('deals/board')->group(function () {
        Route::get('{pipeline}', [DealBoardController::class, 'board']);
        Route::post('{pipeline}', [DealBoardController::class, 'update']);
        Route::get('{pipeline}/summary/{stageId?}', [DealBoardController::class, 'summary']);
        Route::get('{pipeline}/{stageId}', [DealBoardController::class, 'load']);
    });

    Route::get('/pipelines/{pipeline}/stages', [PipelineStageController::class, 'index']);
});
