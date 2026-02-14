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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\GlobalSearch;
use Modules\Core\Resource\Resource;

class GlobalSearchController extends ApiController
{
    /**
     * Perform global search.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        $only = (array) $request->get('only', []);

        $resources = Innoclapps::globallySearchableResources()
            ->when(count($only) > 0, fn ($collection) => $collection->filter(
                fn (Resource $resource) => in_array($resource->name(), $only)
            ));

        return $this->response(
            new GlobalSearch($request, $resources->all())
        );
    }
}
