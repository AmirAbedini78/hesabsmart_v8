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

namespace Modules\Deals\Filters;

use Modules\Core\Filters\Date;
use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\Number;
use Modules\Core\Filters\Numeric;
use Modules\Core\Filters\Operand;

class ResourceDealsFilter extends HasFilter
{
    /**
     * Initialize new ResourceDealsFilter instance.
     */
    public function __construct(string $singularLabel)
    {
        parent::__construct('deals', __('deals::deal.deals'));

        $this->setOperands([
            Operand::from(Numeric::make('amount', __('deals::deal.deal_amount'))),
            Operand::from(Date::make('expected_close_date', __('deals::deal.deal_expected_close_date'))),
            Operand::from(
                Number::make('open_count', __('deals::deal.count.open', ['resource' => $singularLabel]))->countFromRelation('openDeals')
            ),
            Operand::from(
                Number::make('won_count', __('deals::deal.count.won', ['resource' => $singularLabel]))->countFromRelation('wonDeals')
            ),
            Operand::from(
                Number::make('lost_count', __('deals::deal.count.lost', ['resource' => $singularLabel]))->countFromRelation('lostDeals')
            ),
            Operand::from(
                Number::make('closed_count', __('deals::deal.count.closed', ['resource' => $singularLabel]))->countFromRelation('closedDeals')
            ),
        ]);
    }
}
