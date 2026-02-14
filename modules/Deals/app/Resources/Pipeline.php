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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Resources\WithResourceRoutes;
use Modules\Core\Criteria\VisibleModelsCriteria;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Deals\Http\Resources\PipelineResource;
use Modules\Deals\Models\Pipeline as PipelineModel;
use Modules\Deals\Models\Stage;

class Pipeline extends Resource implements WithResourceRoutes
{
    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Deals\Models\Pipeline';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return PipelineResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return VisibleModelsCriteria::class;
    }

    /**
     * Prepare index query.
     */
    public function indexQuery(ResourceRequest $request): Builder
    {
        return parent::indexQuery($request)->with('currentUserSortedModel');
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'stages' => ['sometimes', 'required', 'array'],
            'stages.*.name' => ['required', 'distinct', StringRule::make()],
            'stages.*.win_probability' => ['required', 'integer', 'max:100', 'min:0'],
            'stages.*.display_order' => ['sometimes', 'integer'],

            'name' => ['required', StringRule::make(), UniqueResourceRule::make(static::$model)],
        ];
    }

    public function create(Model $model, ResourceRequest $request): PipelineModel
    {
        $attributes = $request->all();

        $model->fill($attributes)->save();

        if (! $model->isPrimary()) {
            $model->saveVisibilityGroup($attributes['visibility_group'] ?? []);
        }

        foreach ($attributes['stages'] ?? [] as $key => $stage) {
            $model->stages()->create(array_merge($stage, [
                'display_order' => $stage['display_order'] ?? $key + 1,
            ]));
        }

        return $model;
    }

    public function update(Model $model, ResourceRequest $request): PipelineModel
    {
        $attributes = $request->all();

        $model->fill($attributes)->save();

        if (! $model->isPrimary() && ($attributes['visibility_group'] ?? null)) {
            $model->saveVisibilityGroup($attributes['visibility_group']);
        }

        $this->persistStages($model, $attributes['stages'] ?? []);

        return $model;
    }

    /**
     * Update the given stages for the given pipeline.
     */
    protected function persistStages(PipelineModel $pipeline, array $stages): void
    {
        foreach ($stages as $key => $stage) {
            $stage['display_order'] = $stage['display_order'] ?? $key + 1;

            if (! isset($stage['id'])) {
                Stage::create([...$stage, ...['pipeline_id' => $pipeline->id]]);

                continue;
            }

            // We will check if there is a stage with the same name before performing an update
            // when a stage is found, this means the the user re-named the stages instead of re-ordering them
            // for example, create 2 stages "Stage" and "Stage 1", save
            // rename "Stage" to "Stage 1" and "Stage 1" to "Stage" and save, it will fail because of the unique foreign key
            // as the "Stage" is saved first but exists in the database with the same name with different ID, as this
            // stage is not yet updated as it comes later in the loop, in this case, will just add a random name to the confliected
            // stage and later the correct name will be set when the stage comes in the loop.
            $stageModelByName = Stage::where([
                'name' => $stage['name'],
                'pipeline_id' => $pipeline->id,
            ])->first();

            $stageModel = Stage::find($stage['id']);

            if ($stageModelByName?->isNot($stageModel)) {
                Stage::withoutTimestamps(fn () => $stageModelByName->fill(['name' => uniqid()])->save());
            }

            $stageModel->fill($stage)->save();
        }
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     */
    public function validationMessages(): array
    {
        return [
            'stages.*.name.required' => __('validation.required', [
                'attribute' => Str::lower(__('deals::deal.stage.name')),
            ]),
            'stages.*.name.distinct' => __('validation.distinct', [
                'attribute' => Str::lower(__('deals::deal.stage.name')),
            ]),
            'stages.*.win_probability.required' => __('validation.required', [
                'attribute' => Str::lower(__('deals::deal.stage.win_probability')),
            ]),
        ];
    }
}
