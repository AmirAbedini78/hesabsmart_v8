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

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Request;

abstract class ApiController extends Controller
{
    /**
     * General API response.
     */
    public function response($data = [], $status = 200, array $headers = [], $options = 0): JsonResponse
    {
        /**
         * Convert paginated collections to proper format.
         *
         * @see https://stackoverflow.com/questions/57604784/laravel-resource-collection-paginate-json-response-error
         */
        if ($data instanceof AnonymousResourceCollection) {
            $data = $data->toResponse(Request::instance())->getData();
        }

        return response()->json($data, $status, $headers, $options);
    }
}
