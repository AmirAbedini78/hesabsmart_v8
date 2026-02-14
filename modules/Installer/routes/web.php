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

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Installer\Http\Controllers\InstallController;
use Modules\Installer\Http\Controllers\RequirementsController;
use Modules\Installer\Http\Middleware\PreventInstallationWhenInstalled;
use Modules\Installer\Installer;
use Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/requirements', [RequirementsController::class, 'show'])->name('installer.requirements.checker');
    Route::post('/requirements', [RequirementsController::class, 'confirm'])->name('installer.requirements.confirm');
});

Route::prefix(Installer::ROUTE_PREFIX)
    ->middleware(PreventInstallationWhenInstalled::class)
    ->withoutMiddleware([
        PreventRequestsWhenMigrationNeeded::class,
        PreventRequestsWhenUpdateNotFinished::class,
        ValidateCsrfToken::class,
    ])->group(function () {
        Route::get('/', [InstallController::class, 'index'])->name('installer.requirements');
        Route::get('permissions', [InstallController::class, 'permissions'])->name('installer.permissions');

        Route::get('setup', [InstallController::class, 'setup'])->name('installer.setup');
        Route::post('setup', [InstallController::class, 'setupStore'])->name('installer.setup.store');

        Route::get('user', [InstallController::class, 'user'])->name('installer.user');
        Route::post('user', [InstallController::class, 'userStore'])->name('installer.user.create');

        Route::get('database', [InstallController::class, 'database'])->name('installer.database');

        Route::get('finalize', [InstallController::class, 'finalize'])->name('installer.finalize');

        Route::match(['get', 'post'], 'finished', [InstallController::class, 'finished'])->name('installer.finished');
    });
