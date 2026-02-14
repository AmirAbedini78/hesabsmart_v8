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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Permissions;
use Modules\Core\Http\Controllers\ApiController;

class PermissionController extends ApiController
{
    /**
     * Get all registered application permissions.
     */
    public function index(): JsonResponse
    {
        Permissions::createMissing();

        return $this->response([
            'grouped' => Permissions::groups(),
            'all' => Permissions::all(),
        ]);
    }
}
