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

namespace Modules\Contacts\Cards;

use Illuminate\Http\Request;
use Modules\Contacts\Criteria\ViewAuthorizedCompaniesCriteria;
use Modules\Contacts\Models\Company;
use Modules\Core\Charts\Progression;

class CompaniesByDay extends Progression
{
    /**
     * Calculates companies created by day
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByDays($request, Company::criteria(ViewAuthorizedCompaniesCriteria::class));
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
        return __('contacts::company.cards.by_day');
    }
}
