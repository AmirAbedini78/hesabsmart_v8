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

namespace Modules\Deals\Board;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Deals\Models\Stage;

class Board
{
    /**
     * @var string
     */
    const RESOURCE_NAME = 'deals';

    /**
     * Optimize query by selecting fewer columns
     */
    protected array $columns = [
        'id',
        'stage_id',
        'swatch_color',
        'user_id',
        'name',
        'expected_close_date',
        'next_activity_date',
        'updated_at',
        'created_at',
        'amount',
        'status',
    ];

    /**
     * The total number of deals to load per stage
     */
    public static int $perPage = 15;

    /**
     * Initialize new Board instance.
     */
    public function __construct(protected Request $request) {}

    /**
     * Get the board data
     */
    public function data(Builder $query, int $pipelineId, array $pages = []): EloquentCollection
    {
        $pages = array_map('intval', $pages);

        $stages = Stage::where('pipeline_id', $pipelineId)->get();
        $summary = $this->summary($query, $pipelineId);

        return $stages->map(function (Stage $stage) use ($query, $summary, $pages) {
            $deals = $this->getDealsForStage($query, $stage->getKey(), $pages[$stage->getKey()] ?? null);

            $stage->setRelation('deals', $deals);
            $stage->setAttribute('calculated_summary', $summary[$stage->getKey()]);

            return $stage;
        });
    }

    /**
     * Load more details for the given stage
     */
    public function load(Builder $query, int $stageId): Stage
    {
        $stage = Stage::find($stageId);

        $stage->setRelation('deals', $this->getDealsForStage($query, $stageId));

        return $stage;
    }

    protected function getDealsForStage(Builder $baseQuery, int $stageId, ?int $loadTillPage = null): EloquentCollection
    {
        $with = ['tags'];
        $count = [
            'incompleteActivitiesForUser as incomplete_activities_for_user_count',
            'products' => fn (Builder $query) => $query->withoutGlobalScope('displayOrder'),
        ];

        $query = $baseQuery->clone()
            ->select($this->columns)
            ->where('stage_id', $stageId)
            ->withCount($count)
            ->with($with)
            ->criteria($this->createFiltersCriteria());

        $deals = new EloquentCollection(
            $query->paginate(static::$perPage)->items()
        );

        // For refresh, to keep old deals in place
        if ($loadTillPage) {
            $deals = $deals->merge(
                $baseQuery->clone()
                    ->select($this->columns)
                    ->where('stage_id', $stageId)
                    ->whereNotIn('id', $deals->modelKeys())
                    ->criteria($this->createFiltersCriteria())
                    ->withCount($count)
                    ->with($with)
                    ->limit(($loadTillPage * static::$perPage) - count($deals->modelKeys()))
                    ->get()
            );
        }

        return $deals;
    }

    /**
     * Updates the board
     */
    public function update(array $data): void
    {
        $updater = new BoardUpdater($data, $this->request->user());

        $updater->perform();
    }

    /**
     * Get the summary for the board
     */
    public function summary(Builder $query, int $pipelineId, ?int $stageId = null): Collection
    {
        return Stage::summary(
            $query->clone()->criteria($this->createFiltersCriteria()),
            $pipelineId,
            $stageId
        );
    }

    /**
     * Create the criteria instance for the filters
     */
    protected function createFiltersCriteria(): QueryCriteria
    {
        $resource = $this->getResource();

        return $resource->createFiltersCriteria(
            app(ResourceRequest::class)->setResource($resource->name())
        );
    }

    /**
     * Get the deals resource instance
     */
    protected function getResource(): Resource
    {
        return Innoclapps::resourceByName(static::RESOURCE_NAME);
    }
}
