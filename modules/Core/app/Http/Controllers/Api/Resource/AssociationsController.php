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

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Modules\Core\Common\Timeline\Timelineables;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;

class AssociationsController extends ApiController
{
    /**
     * Get the all of the resource associations.
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        $response = [];

        foreach ($request->resource()->associateableResources() as $resource) {
            $associations = $resource->indexQuery($request)->whereHas(
                $request->resource()->associateableName(),
                function (Builder $query) use ($request) {
                    return $query->where($query->getModel()->getKeyName(), $request->resourceId());
                })->paginate($request->perPage());

            $response[$resource->name()] = collect(
                $resource->createJsonResource($associations, true, $request)
            )->map(function (array $data, int $index) {
                $data['is_primary_associated'] = $index === 0;

                return $data;
            })->all();
        }

        return $this->response($response);
    }

    /**
     * Get the resource given resource associated associations.
     */
    public function show(ResourceRequest $request): JsonResponse
    {
        $this->authorize('view', $request->record());

        $associatedResource = $request->findResource($request->associatedResource);

        abort_if(! $associatedResource?->isAssociateable() || ! $associatedResource->jsonResource(), 404);

        $isForTimeline = $request->boolean('timeline');

        abort_if($isForTimeline &&
            (
                ! Timelineables::hasTimeline($request->record()) ||
                ! Timelineables::isTimelineable($associatedResource->newModel())
            ), 404);

        if ($isForTimeline) {
            $query = $associatedResource->timelineQuery($request->record(), $request);
        } else {
            $query = $associatedResource
                ->indexQuery($request)
                ->whereHas($request->resource()->associateableName(), function (Builder $query) use ($request) {
                    return $query->where($request->record()->getKeyName(), $request->resourceId());
                });
        }

        $records = $query->paginate($request->perPage());

        $associatedResource->jsonResource()::topLevelResource($request->record());

        return $this->response(
            $associatedResource->jsonResource()::collection($records)->toResponse($request)->getData()
        );
    }
}
