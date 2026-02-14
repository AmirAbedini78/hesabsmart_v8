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

namespace Modules\Calls\Cards;

use Illuminate\Http\Request;
use Modules\Calls\Models\Call;
use Modules\Core\Charts\Progression;
use Modules\Users\Criteria\QueriesByUserCriteria;

class LoggedCallsByDay extends Progression
{
    /**
     * Calculates logged calls by day
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = (new Call)->newQuery();

        if ($userId = $this->getUserId($request)) {
            $query->criteria(new QueriesByUserCriteria($userId));
        }

        return $this->countByDays($request, $query);
    }

    /**
     * Get the ranges available for the chart.
     */
    public function ranges(): array
    {
        return [
            7 => __('core::dates.periods.7_days'),
            15 => __('core::dates.periods.15_days'),
            30 => __('core::dates.periods.30_days'),
        ];
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('calls::call.cards.by_day');
    }
}
