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
use Modules\MailClient\Http\Controllers\MailTrackerController;
use Modules\MailClient\Http\Controllers\OAuthEmailAccountController;

Route::get('mt/o/{hash}', [MailTrackerController::class, 'opens'])->name('mail-tracker.open');
Route::get('mt/l', [MailTrackerController::class, 'link'])->name('mail-tracker.link');

Route::middleware('auth')->group(function () {
    Route::get('/mail/accounts/{type}/{provider}/connect', [OAuthEmailAccountController::class, 'connect']);
});
