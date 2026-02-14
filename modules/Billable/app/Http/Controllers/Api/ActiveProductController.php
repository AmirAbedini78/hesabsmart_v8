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

namespace Modules\Billable\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Billable\Http\Resources\ProductResource;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;

class ActiveProductController extends ApiController
{
    /**
     * Search for active products
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        abort_if($request->resource()->name() !== 'products', 404);

        $products = $request->resource()
            ->indexQuery($request)
            ->active()
            ->get();

        return $this->response(ProductResource::collection($products));
    }
}
