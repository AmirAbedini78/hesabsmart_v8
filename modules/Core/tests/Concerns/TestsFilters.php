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

namespace Modules\Core\Tests\Concerns;

use Illuminate\Support\Facades\Request;
use Modules\Core\Criteria\FiltersCriteria;
use Modules\Core\Filters\QueryBuilderGroups;
use Tests\Fixtures\Event;

trait TestsFilters
{
    /**
     * Perform filers search
     *
     * @param  Criteria  $criteria
     * @return \Illuminate\Support\Collection
     */
    protected function perform($attribute, $operator, $value = null)
    {
        $filter = app(static::$filter, ['field' => $attribute]);

        $rule = $this->payload(
            $attribute,
            $value,
            $filter->type(),
            $operator
        );

        $groups = new QueryBuilderGroups([$rule], collect([$filter]));

        return (new Event)
            ->newQuery()
            ->criteria(new FiltersCriteria($groups, Request::instance()))
            ->get();
    }

    /**
     * Get filter payload
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  string  $type
     * @param  string  $operator
     */
    protected function payload($field, $value, $type, $operator): array
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => $type,
                'rule' => $field,
                'operator' => $operator,
                'value' => $value,
            ],
        ];

        return [
            'condition' => 'all',
            'children' => [$rule],
        ];
    }
}
