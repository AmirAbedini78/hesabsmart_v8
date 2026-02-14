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

namespace Modules\Billable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\Number;
use Modules\Core\Filters\Operand;
use Modules\Core\Filters\QueryBuilder;
use Modules\Core\Filters\Text;

class BillableProductsFilter extends HasFilter
{
    /**
     * Initialize new BillableProductsFilter instance.
     */
    public function __construct()
    {
        parent::__construct('products', __('billable::product.product'));

        $this->setOperands([
            Operand::from(Number::make('total_count', __('billable::product.total_products'))->countFromRelation('products')),
            Operand::from(Text::make('name', __('billable::product.name'))->withoutNullOperators()),
            Operand::from(Number::make('qty', __('billable::product.quantity'))),
            Operand::from(Text::make('unit', __('billable::product.unit'))),
            Operand::from(Text::make('sku', __('billable::product.sku'))->applyQueryUsing(
                function (Builder $query, string $boolean, Text $filter, QueryBuilder $builder) {
                    return $query->whereHas(
                        'originalProduct',
                        function (Builder $query) use ($boolean, $filter, $builder) {
                            $builder->applyFilterOperatorQuery($query, $filter, $boolean);
                        }
                    );
                })
            ),
        ]);
    }
}
