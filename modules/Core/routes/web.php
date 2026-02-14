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

use Illuminate\Http\Middleware\CheckResponseForModifications;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\MediaViewController;
use Modules\Core\Http\Controllers\OAuthController;
use Modules\Core\Http\Controllers\PrivacyPolicy;
use Modules\Core\Http\Controllers\ScriptController;
use Modules\Core\Http\Controllers\ServeApplication;
use Modules\Core\Http\Controllers\StyleController;
use Modules\Core\Http\Controllers\SynchronizationGoogleWebhookController;
use Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished;

Route::post('/webhook/google', [SynchronizationGoogleWebhookController::class, 'handle']);

Route::withoutMiddleware(CheckResponseForModifications::class)->group(function () {
    Route::get('/scripts/{script}', [ScriptController::class, 'show']);
    Route::get('/styles/{style}', [StyleController::class, 'show']);
});

Route::withoutMiddleware([
    PreventRequestsWhenMigrationNeeded::class,
    PreventRequestsWhenUpdateNotFinished::class,
])->group(function () {
    Route::get('privacy-policy', PrivacyPolicy::class);

    Route::get('/media/{token}', [MediaViewController::class, 'show']);
    Route::get('/media/{token}/download', [MediaViewController::class, 'download']);
    Route::get('/media/{token}/preview', [MediaViewController::class, 'preview']);
});

Route::middleware('auth')->group(function () {
    Route::get('/{providerName}/connect', [OAuthController::class, 'connect'])->where('providerName', 'microsoft|google');
    Route::get('/{providerName}/callback', [OAuthController::class, 'callback'])->where('providerName', 'microsoft|google');
});

Route::middleware('auth')->group(function () {
    Route::fallback(ServeApplication::class);
});
