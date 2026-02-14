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

use Illuminate\Support\Collection;

class QueryBuilderChildGroup
{
    /**
     * The group children instances.
     */
    protected ?array $children = null;

    /**
     * Initialize new Group instance.
     *
     * @param  array  $group  The group applied to the query builder.
     * @param  Collection<object, Filter|Operand>  $filters  The available filters.
     */
    public function __construct(protected array $group, protected Collection $filters) {}

    /**
     * Get all of the group children.
     *
     * @return array<QueryBuilderChildGroup|Filter|OperandFilter>
     */
    public function children(): array
    {
        return $this->children ??= collect($this->group['children'] ?? [])->map(function (array $children) {
            $query = $children['query'];
            $type = $children['type'];

            if ($type === 'group') { // is group
                return new QueryBuilderChildGroup($query, $this->filters);
            } elseif ($type === 'rule') {
                if (isset($query['operand']) && $query['operand']) { // Is operand
                    return $this->populateOperandRule($query);
                } elseif ($rule = $this->findRule($query['rule'])) {  // Is regular rule
                    // Clone the rule as this rule may exists multiple times in the group
                    return $this->populateRule(clone $rule, $query);
                }
            }
        })->filter()->values()->all();
    }

    /**
     * Check whether the group has any rules.
     */
    public function hasRules(): bool
    {
        return collect($this->children())->some(function ($child) {
            if ($child instanceof QueryBuilderChildGroup) {
                return $child->hasRules();
            }

            return true;
        });
    }

    /**
     * Populate the operand rule with data.
     */
    protected function populateOperandRule(array $query): ?OperandFilter
    {
        $filter = $this->operands()->first(
            fn (OperandFilter $filter) => $filter->field() === $query['rule']
        );

        if (! $filter) {
            return null;
        }

        // Set the operand value so the OperandFilter instance know which operand is active.
        $filter->setOperand($query['operand']);

        if ($this->operatorRequiresValue($query['operator'])) {
            $filter->setValue($query['value'] ?? null);
        }

        // If set, populate the operand filter instance.
        if ($operand = $filter->getOperandInstance()) {
            $this->populateRule($operand->getFilter(), $query);
        }

        return $filter;
    }

    /**
     * Get all of the operand in the group.
     *
     * @return Collection<object, OperandFilter>
     */
    protected function operands(): Collection
    {
        return $this->filters->whereInstanceOf(OperandFilter::class);
    }

    /**
     * Populate the rule data.
     */
    protected function populateRule(Filter $rule, array $query): Filter
    {
        // Static rules does not have any values nor operators.
        if ($rule->isStatic()) {
            return $rule->setValue(null);
        }

        $value = $query['value'] ?? null;
        $operator = $query['operator'] ?? null;

        // We will set the operator in the filter so it can be used in the "prepareValue" method.
        $rule->setOperator($operator);

        if ($this->operatorRequiresValue($operator) && method_exists($rule, 'prepareValue')) {
            $value = $rule->prepareValue($value);
        }

        $rule->setValue($value);

        return $rule;
    }

    /**
     * Find rule from the available rules list.
     */
    public function findRule(string $rule): ?Filter
    {
        return $this->filters->first(
            fn (Filter $filter) => $filter->field() === $rule
        );
    }

    /**
     * Check if the given operator requires value.
     */
    protected function operatorRequiresValue(string $operator): bool
    {
        return ! in_array($operator, ['is_null', 'is_not_null']);
    }

    /**
     * Check whether the group is nested.
     */
    public function isNested(): bool
    {
        return count($this->group['children'] ?? []) > 0;
    }

    /**
     * Get the group query condition (boolean).
     */
    public function boolean(): string
    {
        $condition = $this->group['condition'];
        if ($condition === 'all') {
            return 'and';
        } elseif ($condition === 'any') {
            return 'or';
        }

        return $condition ?? 'all';
    }

    /**
     * Check whether the group is the quick group.
     */
    public function isQuick(): bool
    {
        return $this->group['quick'] ?? false;
    }
}
