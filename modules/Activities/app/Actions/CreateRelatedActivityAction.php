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

namespace Modules\Activities\Actions;

use Illuminate\Support\Collection;
use Modules\Activities\Models\Activity;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\CreateResourceRequest;
use Modules\Core\Http\Requests\ResourceRequest;

class CreateRelatedActivityAction extends Action
{
    /**
     * The action modal size. (sm, md, lg, xl, xxl)
     */
    public string $size = 'md';

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): void
    {
        $activityResource = Innoclapps::resourceByModel(Activity::class);

        /** @var CreateResourceRequest */
        $request = app(CreateResourceRequest::class)->setResource($activityResource->name());

        foreach ($models as $model) {
            $request->replace($fields->all());
            $relation = $model->resource()->associateableName();

            if ($request->has($relation)) {
                $request->merge([
                    $relation => $request->collect($relation)->push($model->getKey())->unique()->all(),
                ]);
            } else {
                $request->merge([$relation => [$model->getKey()]]);
            }

            $activityResource->create(new Activity, $request);
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        if (is_callable($this->canRunCallback)) {
            return parent::authorizedToRun($request, $model);
        }

        return $request->user()->can('update', $model);
    }

    /**
     * Get the action fields.
     */
    public function fields(ResourceRequest $request): array
    {
        return Innoclapps::resourceByModel(Activity::class)->visibleFieldsForCreate()->all();
    }

    /**
     * Get the confirmation button text.
     */
    public function confirmButtonText(): string
    {
        return __('core::app.create');
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('activities::activity.create');
    }
}
