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
use Modules\Core\Http\Requests\ResourceRequest;

trait ResolvesActions
{
    /**
     * Get the available actions for the resource
     *
     * @return \Illuminate\Support\Collection<object, \Modules\Core\Actions\Action>
     */
    public function resolveActions(ResourceRequest $request): Collection
    {
        $actions = $this->actions($request);

        $collection = is_array($actions) ? new Collection($actions) : $actions;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined resource actions
     */
    public function actions(ResourceRequest $request): array|Collection
    {
        return [];
    }
}
