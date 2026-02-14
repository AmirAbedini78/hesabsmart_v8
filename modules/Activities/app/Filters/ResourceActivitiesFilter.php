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

namespace Modules\Activities\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Activities\Models\Activity;
use Modules\Core\Filters\QueryBuilder;
use Modules\Core\Filters\Select;

class ResourceActivitiesFilter extends Select
{
    /**
     * Initialize new ResourceActivitiesFilter instance.
     */
    public function __construct()
    {
        parent::__construct('activities', __('activities::activity.activity'), ['equal']);

        $this->options([
            'today' => __('core::dates.due.today'),
            'next_day' => __('core::dates.due.tomorrow'),
            'this_week' => __('core::dates.due.this_week'),
            'next_week' => __('core::dates.due.next_week'),
            'this_month' => __('core::dates.due.this_month'),
            'next_month' => __('core::dates.due.next_month'),
            'this_quarter' => __('core::dates.due.this_quarter'),
            'overdue' => __('activities::activity.overdue'),
            'doesnt_have_activities' => __('activities::activity.doesnt_have_activities'),
        ])->displayAs([
            __('activities::activity.filters.display.has'),
            'overdue' => __('activities::activity.filters.display.overdue'),
            'doesnt_have_activities' => __('activities::activity.filters.display.doesnt_have_activities'),
        ]);
    }

    /**
     * Apply the filter to the given query.
     */
    public function apply(Builder $query, string $boolean, QueryBuilder $builder): Builder
    {
        $value = $this->getValue();

        if ($value == 'doesnt_have_activities') {
            return $query->doesntHave('activities', $boolean);
        }

        return $query->has('activities', '>=', 1, $boolean, function (Builder $query) use ($value) {
            if ($value === 'overdue') {
                return $query->overdue();
            }

            return $query->whereDateRange(Activity::dueDateQueryExpression(), $value);
        });
    }
}
