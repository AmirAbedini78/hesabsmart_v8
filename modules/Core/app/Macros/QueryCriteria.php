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

namespace Modules\Core\Macros;

use Closure;
use Modules\Core\Contracts\Criteria\QueryCriteria as QueryCriteriaContract;

class QueryCriteria
{
    public function criteria(): Closure
    {
        return function ($criteria) {
            /** @var \Illuminate\Database\Eloquent\Builder */
            $builder = $this;

            if ($criteria instanceof QueryCriteriaContract || is_string($criteria)) {
                $criteria = [$criteria];
            }

            if (is_iterable($criteria)) {
                foreach ($criteria as $instance) {
                    if (is_string($instance)) {
                        $instance = new $instance;
                    }

                    $instance->apply($builder);
                }
            }

            return $builder;
        };
    }
}
