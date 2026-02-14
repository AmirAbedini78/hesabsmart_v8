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

namespace Modules\Deals\Actions;

use Illuminate\Support\Collection;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Deals\Models\Deal;

class MarkAsOpen extends Action
{
    /**
     * Indicates that the action will be shown on the detail view.
     */
    public bool $showOnDetail = false;

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): void
    {
        $models->reject(fn (Deal $model) => $model->isOpen())->each(function (Deal $model) {
            $model->markAsOpen();
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('update', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('deals::deal.actions.mark_as_open');
    }
}
