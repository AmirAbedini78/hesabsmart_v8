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

namespace Modules\Updater\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Installer\RequirementsChecker;
use Modules\Updater\Exceptions\UpdaterException;
use Modules\Updater\Updater;
use Throwable;

class UpdateController extends ApiController
{
    /**
     * Get information about update.
     */
    public function index(Updater $updater): JsonResponse
    {
        return $this->response([
            'installed_version' => $updater->getVersionInstalled(),
            'latest_available_version' => $updater->getVersionAvailable(),
            'is_new_version_available' => $updater->isNewVersionAvailable(),
            'purchase_key' => $updater->getPurchaseKey(),
        ]);
    }

    /**
     * Perform an application update.
     */
    public function update(Request $request, RequirementsChecker $requirements): JsonResponse
    {
        // Update flag
        $purchaseKey = $request->input('purchase_key');

        // Save the purchase key for future usage
        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        abort_if(
            $requirements->fails('zip'),
            JsonResponse::HTTP_CONFLICT,
            __('updater::update.update_zip_is_required')
        );

        /** @var \Modules\Updater\Updater */
        $updater = app(Updater::class);

        $updater->usePurchaseKey($purchaseKey ?: '');

        if (! $updater->isNewVersionAvailable()) {
            throw new UpdaterException('No new version available', JsonResponse::HTTP_CONFLICT);
        }

        if (! app()->runningUnitTests()) {
            $updater->increasePhpIniValues();
        }

        Artisan::call('down', ['--render' => 'updater::errors.updating']);

        try {
            $updater->update($updater->getVersionAvailable());
        } catch (Throwable $e) {
            Artisan::call('up');

            throw $e;
        } finally {
            Artisan::call('up');
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
