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

use Modules\Core\Facades\Timezone as Facade;

class Timezone extends Optionable
{
    /**
     * Resolve the filter options.
     */
    public function resolveOptions(): array
    {
        return collect(Facade::toArray())->map(function ($timezone) {
            return [$this->labelKey => $timezone, $this->valueKey => $timezone];
        })->all();
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'select';
    }
}
