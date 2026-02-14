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
use Modules\Core\Http\Controllers\ApiController;
use Modules\Installer\RequirementsChecker;
use Modules\Updater\Exceptions\UpdaterException;
use Modules\Updater\Patcher;

class PatchController extends ApiController
{
    /**
     * Get the available patches for the installed version.
     */
    public function index(Patcher $patcher): JsonResponse
    {
        return $this->response($patcher->getAvailablePatches());
    }

    /**
     * Apply the given patch to the current installed version.
     */
    public function apply(Request $request, RequirementsChecker $requirements, ?string $token = null): JsonResponse
    {
        // Apply patch flag

        $purchaseKey = $request->input('purchase_key');

        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        abort_unless(
            $requirements->passes('zip'),
            JsonResponse::HTTP_CONFLICT,
            __('updater::update.patch_zip_is_required')
        );

        /** @var \Modules\Updater\Patcher */
        $patcher = app(Patcher::class);

        $patcher->usePurchaseKey($purchaseKey ?: '');

        if (! $token) {
            $patcher->applyAll();
        } else {
            $patch = $patcher->find($token);

            if ($patch->isApplied()) {
                throw new UpdaterException('This patch is already applied.', JsonResponse::HTTP_CONFLICT);
            }

            $patcher->apply($patch);
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
