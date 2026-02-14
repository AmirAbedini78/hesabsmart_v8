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

namespace Modules\Core\Http\Controllers\Api\Module;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Module\Module;
use Modules\Core\Module\ModuleUploadException;

class UploadModule extends ApiController
{
    /**
     * Handle module(s) upload.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Module upload flag

        $request->validate([
            'file' => 'required|extensions:zip|mimes:zip',
        ]);

        try {
            $result = Module::upload($request->file('file'));

            return response()->json($result);
        } catch (ModuleUploadException $e) {
            return $this->response(['message' => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }
}
