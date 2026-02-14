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
use Modules\Core\Contracts\Resources\Cloneable;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;

class CloneController extends ApiController
{
    /**
     * Clone a resource record
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        /** @var \Modules\Core\Resource\Resource&\Modules\Core\Contracts\Resources\Cloneable */
        $resource = $request->resource();

        abort_unless($resource instanceof Cloneable, 404);

        $this->authorize('view', $request->record());

        $record = $resource->clone($request->record(), (int) $request->user()->getKey());

        return $this->response($request->toResponse(
            $resource->displayQuery()->find($record->getKey())
        ));
    }
}
