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

namespace Modules\Deals\Cards;

use Illuminate\Http\Request;
use Modules\Core\Charts\Progression;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Models\Deal;
use Modules\Users\Criteria\QueriesByUserCriteria;

class WonDealsByDay extends Progression
{
    /**
     * Calculates won deals by day
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $query = Deal::won()->criteria(ViewAuthorizedDealsCriteria::class);

        if ($userId = $this->getUserId($request)) {
            $query->criteria(new QueriesByUserCriteria($userId));
        }

        return $this->countByDays($request, $query, 'won_date');
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
            60 => __('core::dates.periods.60_days'),
        ];
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('deals::deal.cards.won_by_date');
    }

    /**
     * Check whether the current user can perform user filter.
     */
    public function authorizedToFilterByUser(): bool
    {
        return request()->user()->canAny(['view all deals', 'view team deals']);
    }
}
