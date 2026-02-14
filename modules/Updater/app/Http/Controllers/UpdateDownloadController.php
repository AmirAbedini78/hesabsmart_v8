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

namespace Modules\Updater\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Updater\Patcher;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UpdateDownloadController extends Controller
{
    /**
     * Download the given patch
     */
    public function downloadPatch(string $token, ?string $purchaseKey = null): BinaryFileResponse
    {
        // Download patch flag

        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        $patcher = app(Patcher::class);

        return $patcher->download($token);
    }
}
