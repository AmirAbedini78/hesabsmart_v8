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
use Illuminate\Support\Facades\DB;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\CreateResourceRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Requests\UpdateResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\AssociatesResources;

class ResourceController extends ApiController
{
    use AssociatesResources;

    /**
     * Display a listing of the resource.
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof WithResourceRoutes, 404);

        // Resource index flag
        $this->authorize('viewAny', $request->resource()::$model);

        $query = $request->resource()->indexQuery($request);

        $results = $query->paginate($request->perPage());

        return $this->response(
            $request->toResponse($results)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateResourceRequest $request): JsonResponse
    {
        $resource = $request->resource();

        abort_if(! $resource instanceof WithResourceRoutes, 404);

        // Resource store flag
        $this->authorize('create', $resource::$model);

        $request->performValidation();

        $recordId = DB::transaction(function () use ($request, $resource) {
            $record = $resource->create($resource->newModel(), $request);

            $this->handleCustomAssociatedResources($record, $request);

            return $record;
        })->getKey();

        $record = $resource->displayQuery()->find($recordId);
        $record->loadMissing($request->getWith());
        $record->wasRecentlyCreated = true;

        return $this->response($request->toResponse($record), JsonResponse::HTTP_CREATED);
    }

    /**
     * Display resource record.
     */
    public function show(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof WithResourceRoutes, 404);

        // Resource show flag
        $this->authorize('view', $request->record());

        $record = $request->resource()->displayQuery()->findOrFail($request->resourceId());

        $record->loadMissing($request->getWith());

        return $this->response($request->toResponse($record));
    }

    /**
     * Update resource record in storage.
     */
    public function update(UpdateResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof WithResourceRoutes, 404);

        // Resource update flag
        $this->authorize('update', $request->record());

        $request->performValidation();

        Db::transaction(function () use ($request) {
            $record = $request->resource()->update($request->record(), $request);

            $this->handleCustomAssociatedResources($record, $request);
        });

        return $this->response(
            $request->toResponse($request->resource()->displayQuery()
                ->with($request->getWith())
                ->find($request->resourceId()))
        );
    }

    /**
     * Remove resource record from storage.
     */
    public function destroy(ResourceRequest $request): JsonResponse
    {
        abort_if(! $request->resource() instanceof WithResourceRoutes, 404);

        // Resource destroy flag
        $this->authorize('delete', $request->record());

        DB::transaction(function () use ($request) {
            $request->resource()->delete($request->record(), $request);
        });

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Sync the given record associations.
     */
    protected function handleCustomAssociatedResources(Model $record, ResourceRequest $request): Model
    {
        if ($request->resource()->isAssociateable()) {
            $associations = $this->authorizeAssociations(
                $request->resource(),
                $request->associateables()
            );

            $this->syncAssociations($request->resource(), $record->getKey(), $associations);
        }

        return $record;
    }
}
