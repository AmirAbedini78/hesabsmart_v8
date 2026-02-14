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

class SearchInGoogleAction extends Action
{
    /**
     * Indicates that this action is without confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * Indicates if the action intended to be run on one resource only.
     */
    public bool $sole = true;

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): array
    {
        $model = $models->first();

        return static::openInNewTab('https://www.google.com/search?q='.urlencode($model::resource()->titleFor($model)));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        if (is_callable($this->canRunCallback)) {
            return parent::authorizedToRun($request, $model);
        }

        return $request->user()->can('view', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('core::actions.search_in_google');
    }
}
