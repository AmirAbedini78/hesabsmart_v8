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
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Support\LogReader;
use Modules\Core\SystemInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemController extends ApiController
{
    /**
     * Get the system info
     */
    public function info(Request $request): JsonResponse
    {
        // System info flag

        return $this->response(new SystemInfo($request));
    }

    /**
     * Download the system info
     */
    public function downloadInfo(Request $request): BinaryFileResponse
    {
        // System info download flag

        return Excel::download(new SystemInfo($request), 'system-info.xlsx');
    }

    /**
     * Get the application/Laravel logs
     */
    public function logs(Request $request): JsonResponse
    {
        // System logs flag

        return $this->response(
            new LogReader(['date' => $request->date])
        );
    }
}
