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
use Modules\Core\Http\Requests\ActionRequest;

class CloneAction extends Action
{
    /**
     * Indicates that the action does not have confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): mixed
    {
        foreach ($models as $model) {
            $model->resource()->clone($model, auth()->id());
        }

        if ($models->containsOneItem()) {
            $model = $models->sole();

            return static::navigateTo(
                sprintf('/%s/%s/edit', $model->resource()->name(), $model->getKey())
            );
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

        return $request->user()->can('create', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('core::app.clone');
    }
}
