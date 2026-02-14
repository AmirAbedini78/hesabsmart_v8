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

namespace Modules\Core\Filters;

use Illuminate\Support\Collection;
use Modules\Core\Http\Requests\ResourceRequest;

trait ResolvesFilters
{
    /**
     *  Get the available filters for the user
     *
     * @return \Illuminate\Support\Collection<object, \Modules\Core\Filters\Filter>
     */
    public function resolveFilters(ResourceRequest $request): Collection
    {
        $filters = $this->filters($request);

        $collection = is_array($filters) ? new Collection($filters) : $filters;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined filters
     */
    public function filters(ResourceRequest $request): array|Collection
    {
        return [];
    }
}
