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

use Illuminate\Contracts\Support\Arrayable;
use Modules\Core\Settings\Utilities\Arr;

class FilterGroups implements Arrayable
{
    /**
     * Initialize new FilterGroups instance.
     *
     * @param  FilterChildGroup|array<array-key, FilterChildGroup>  $childGroups
     */
    public function __construct(
        protected FilterChildGroup|array $childGroups,
    ) {}

    /**
     * Get an array representation of the filter group.
     *
     * @return array<array-key, array>
     */
    public function toArray()
    {
        return collect(Arr::wrap($this->childGroups))->map(
            fn (FilterChildGroup $childGroup) => $childGroup->toArray()
        );
    }
}
