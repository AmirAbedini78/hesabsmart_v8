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

namespace Modules\Core\Actions;

use Illuminate\Support\Collection;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Requests\UpdateResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;

class BulkEditAction extends Action
{
    /**
     * Indicates that the action will be shown on the detail view.
     */
    public bool $showOnDetail = false;

    /**
     * The action modal size. (sm, md, lg, xl, xxl)
     */
    public string $size = 'md';

    /**
     * Initialize new BulkEditAction instance.
     */
    public function __construct(protected Resource $resource)
    {
        parent::__construct();
    }

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): void
    {
        foreach ($models as $model) {
            $request = $this->createUpdateRequest($model, $fields->all());

            $this->resource->update($model, $request);
        }
    }

    /**
     * Create update request for the action.
     */
    protected function createUpdateRequest(Model $model, array $data): UpdateResourceRequest
    {
        return app(UpdateResourceRequest::class)
            ->setRecord($model)
            ->replace($data)
            ->setResource($this->resource->name())
            ->setResourceId($model->getKey());
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
        return $this->resource->visibleFieldsForUpdate()->each(function (Field $field) {
            $field->prepareForBulkEdit();
        })->reject(
            fn (Field $field) => $field->isUnique() || $field->excludeFromBulkEdit
        )->all();
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('core::actions.bulk_edit');
    }

    /**
     * Get the component the action should use.
     */
    public function component(): string
    {
        return 'action-bulk-edit-modal';
    }
}
