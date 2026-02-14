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

use Illuminate\Database\Eloquent\Builder;

class HasFilter extends OperandFilter
{
    protected bool $checkRelationExistence = false;

    /**
     * Apply the filter to the given query.
     */
    public function apply(Builder $query, string $boolean, QueryBuilder $builder): Builder
    {
        if ($this->getOperandInstance()->getFilter() instanceof RelationCountBasedFilter) {
            return $builder->applyCountRelationshipQuery(
                $query,
                $this->getOperandInstance()->getFilter(),
                $boolean,
                function (Builder $query) {
                    $this->applyViewAuthorizedCriteriaIfNeeded($query);
                }
            );
        }

        $operator = $this->getOperandInstance()->getFilter()->getOperator();

        if ($this->checkRelationExistence && $operator === 'is_null') {
            return $query->whereDoesntHave($this->field());
        } elseif ($this->checkRelationExistence && $operator === 'is_not_null') {
            return $query->whereHas($this->field());
        } else {
            return $query->has($this->field(), '>=', 1, $boolean, function (Builder $query) use ($builder) {
                $this->applyViewAuthorizedCriteriaIfNeeded($query);

                // Use AND for the subquery of the relation rules
                return $builder->applyQuery($query, $this->getOperandInstance()->getFilter(), 'AND');
            });
        }
    }

    /**
     * Set that relation existence will be checked for "is_null" and "is_not_null" operands.
     */
    public function checkRelationExistence(): static
    {
        $this->checkRelationExistence = true;

        return $this;
    }

    /**
     * Apply view authorized criteria to the builder if the builder model is associated with resources
     */
    protected function applyViewAuthorizedCriteriaIfNeeded(Builder $query): Builder
    {
        if (
            method_exists($query->getModel(), 'resource') &&
            $criteria = $query->getModel()->resource()->viewAuthorizedRecordsCriteria()
        ) {
            (new $criteria)->apply($query);
        }

        return $query;
    }
}
