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

namespace Modules\Deals\Cards;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Modules\Core\Charts\ChartResult;
use Modules\Core\Charts\Presentation;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;

abstract class DealPresentationCard extends Presentation
{
    /**
     * Stages cache.
     */
    protected ?Collection $stages = null;

    /**
     * Add stages labels to the result.
     */
    protected function withStageLabels(ChartResult $result): ChartResult
    {
        return $result->label(
            fn ($value) => $this->stages()->find($value)->name
        );
    }

    /**
     * Get the deals pipeline for the card.
     */
    protected function getPipelineId(Request $request): int
    {
        $pipelineId = $request->filled('pipeline_id') ? $request->integer('pipeline_id') : null;
        $pipeline = null;

        if ($pipelineId) {
            $pipeline = Pipeline::visible()->find($pipelineId) ?? $this->getPrimaryPipeline();
        } else {
            $pipeline = $this->getPrimaryPipeline();
        }

        return $pipeline->getKey();
    }

    /**
     * Get the primary pipeline.
     */
    protected function getPrimaryPipeline(): Pipeline
    {
        return Pipeline::visible()->orderByUserSpecified(RequestFacade::user())->first();
    }

    /**
     * Get all available stages.
     */
    protected function stages(): Collection
    {
        return $this->stages ??= Stage::select(['id', 'name', 'display_order'])->orderByDisplayOrder()->get();
    }

    /**
     * Sort the given result by stages display order.
     */
    protected function sortResultByStagesDisplayOrder(array &$result): array
    {
        $displayOrderMap = [];

        foreach ($this->stages() as $stage) {
            $displayOrderMap[$stage->id] = $stage->display_order;
        }

        uksort($result, function ($a, $b) use ($displayOrderMap) {
            return $displayOrderMap[$a] <=> $displayOrderMap[$b];
        });

        return $result;
    }

    /**
     * The element's component.
     */
    public function component(): string
    {
        return 'deal-presentation-card';
    }

    /**
     * Get the cache key for the card.
     */
    public function getCacheKey(Request $request): string
    {
        return sprintf(
            parent::getCacheKey($request).'.%s',
            $this->getPipelineId($request),
        );
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'pipeline_id' => $this->getPipelineId(RequestFacade::instance()),
        ]);
    }
}
