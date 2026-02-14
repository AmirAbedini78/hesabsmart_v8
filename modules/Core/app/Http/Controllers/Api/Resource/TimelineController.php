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
use Illuminate\Http\Request;
use Modules\Core\Common\Timeline\Timelineables;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Resources\ChangelogResource;
use Modules\Core\Models\PinnedTimelineSubject;
use Modules\Core\Resource\Resource;

class TimelineController extends ApiController
{
    /**
     * Get the resource changelog.
     */
    public function index(Request $request, string $resourceName, string $recordId): JsonResponse
    {
        $resource = Innoclapps::resourceByName($resourceName);
        $record = $resource->newModel()->findOrFail($recordId);
        $hasChangelog = $record->isRelation('changelog');

        $resources = $this->getResourcesForChangelog();

        // When there is no resources included for the changelog and
        // the resource record does not have the changelog relation
        // in this case, 404 error will be shown
        if ($resources->isEmpty()) {
            abort_unless($hasChangelog, 404);
        }

        $this->authorize('view', $record);

        $changelog = collect([])->when($hasChangelog, function ($collection) use ($record, $request) {
            ChangelogResource::topLevelResource($record);

            return $this->resolveChangelogJsonResource($record, $request);
        })->when(true, function ($collection) use ($record, $request) {
            $this->resolveResourcesJsonResource($record, $request)
                ->each(function ($data) use ($collection) {
                    $collection->push(...$data);
                });

            return $collection;
        })->sortBy([['is_pinned', 'desc'], ['pinned_date', 'desc'], [function ($record) {
            return $record['timeline_sort_column'];
        }, 'desc']]);

        return $this->response([
            'data' => $changelog->values()->all(),
            'resources' => $resources->map(fn (Resource $resource) => [
                'name' => $resource->name(),
                'label' => $resource->label(),
                'timeline_relation' => $resource->newModel()->getTimelineRelation(),
            ]),
        ]);
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param  \Modules\Core\Models\Model  $record
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function resolveChangelogJsonResource($record, $request)
    {
        $changelogModel = $record->changelog()->getModel();

        $columns = $changelogModel->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($changelogModel->getTable());

        $query = $record->changelog()
            ->select($changelogModel->qualifyColumns($columns))
            ->with('pinnedTimelineSubjects')
            ->withTimelinePins($record)
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc')
            ->orderBy($record->changelog()->getModel()->getQualifiedCreatedAtColumn(), 'desc');

        return collect(ChangelogResource::collection(
            $query->paginate($request->perPage())
        )->resolve());
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param  \Modules\Core\Models\Model  $record
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Support\Collection
     */
    protected function resolveResourcesJsonResource($record, $request)
    {
        return $this->getResourcesForChangelog($request)->map(function (Resource $resource) use ($record, $request) {
            $resource->jsonResource()::topLevelResource($record);

            return $resource->createJsonResource(
                $resource->timelineQuery(
                    $record,
                    app(ResourceRequest::class)->setResource($resource->name())
                )->paginate($request->perPage()),
                true
            );
        });
    }

    /**
     * Get the resources that should be added in the changelog
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getResourcesForChangelog()
    {
        return collect((new Timelineables)->getTimelineables())
            ->map(fn (string $model) => Innoclapps::resourceByModel($model))
            ->filter()
            ->values();
    }
}
