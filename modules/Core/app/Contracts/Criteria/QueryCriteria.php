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

namespace Modules\Core\Contracts\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $builder);
}
