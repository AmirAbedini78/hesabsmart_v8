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
use Modules\Brands\Http\Controllers\Api\BrandController;
use Modules\Brands\Http\Controllers\Api\BrandLogoController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands/{brand}/logo/{type}', [BrandLogoController::class, 'store'])->where('type', 'mail|view');
    Route::delete('/brands/{brand}/logo/{type}', [BrandLogoController::class, 'delete'])->where('type', 'mail|view');

    Route::apiResource('brands', BrandController::class);
});
