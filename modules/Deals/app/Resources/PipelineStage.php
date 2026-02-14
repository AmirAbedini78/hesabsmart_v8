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

namespace Modules\Deals\Resources;

use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Deals\Http\Resources\StageResource;
use Modules\Deals\Models\Stage;

class PipelineStage extends Resource implements WithResourceRoutes
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Deals\Models\Stage';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return StageResource::class;
    }

    /**
     * Create new resource record in storage.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        $model->fill($request->all())->save();

        return $model;
    }

    /**
     * Update resource record in storage.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        $model->fill($request->all())->save();

        return $model;
    }

    /**
     * Get the resource rules available for create
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'pipeline_id' => ['required', 'numeric'],
            'name' => array_filter([
                'required',
                StringRule::make(),
                // Validate after the pipeline_id is provided
                $request->filled('pipeline_id') ? UniqueResourceRule::make(
                    Stage::class,
                    'resourceId'
                )->where('pipeline_id', $request->integer('pipeline_id')) : null,
            ]),
            'win_probability' => ['required', 'integer', 'max:100', 'min:0'],
            'display_order' => ['sometimes', 'integer'],
        ];
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return ['name' => 'like'];
    }
}
