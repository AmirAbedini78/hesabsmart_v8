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

use Carbon\CarbonPeriod;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Core\Concerns\HasDisplayOrder;
use Modules\Core\Filters\Exceptions\QueryBuilderException;

class QueryBuilder
{
    /**
     * Available rules operators.
     */
    public static array $operators = [
        'is' => ['apply_to' => ['date']], // the filter must implement "apply" method
        'equal' => ['apply_to' => ['text', 'number', 'numeric', 'date', 'radio', 'select']],
        'not_equal' => ['apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
        'in' => ['apply_to' => ['multi-select', 'checkbox']],
        'not_in' => ['apply_to' => ['multi-select']],
        'less' => ['apply_to' => ['number', 'numeric', 'date']],
        'less_or_equal' => ['apply_to' => ['number', 'numeric', 'date']],
        'greater' => ['apply_to' => ['number', 'numeric', 'date']],
        'greater_or_equal' => ['apply_to' => ['number', 'numeric', 'date']],
        'between' => ['apply_to' => ['number', 'numeric', 'date']],
        'not_between' => ['apply_to' => ['number', 'numeric', 'date']],
        'begins_with' => ['apply_to' => ['text']],
        'not_begins_with' => ['apply_to' => ['text']],
        'contains' => ['apply_to' => ['text']],
        'not_contains' => ['apply_to' => ['text']],
        'ends_with' => ['apply_to' => ['text']],
        'not_ends_with' => ['apply_to' => ['text']],
        'is_null' => ['apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
        'is_not_null' => ['apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
    ];

    /**
     * The operators that requires array value.
     *
     * @var string[]
     */
    public $needsArray = ['between', 'not_between', 'in', 'not_in'];

    /**
     * Initialize new QueryBuilder instance.
     */
    public function __construct(protected QueryBuilderGroups $groups) {}

    /**
     * Apply the query builder rules to the given query.
     */
    public function apply(Builder $query): Builder
    {
        // The query filters group is always list, we will make sure that it's always sorted last.
        $groups = collect($this->groups->all())->sortBy(function ($group) {
            return $group->isQuick() ? 1 : 0;
        });

        foreach ($groups as $group) {
            if ($group->isQuick() === false) {
                $query->where(function ($query) use ($group) {
                    $query = $this->loopThroughRules($group, $query);
                }, null, null, 'and');
            } else {
                // For quick group, we will immediately create nested query to wrap the rules in a isolated
                // where clause as the quick filters may contain multiple rules as well.
                $query = $this->createNestedQuery($query, $group, $group->boolean());
            }
        }

        return $query;
    }

    /**
     * Loop through the condition rules.
     */
    protected function loopThroughRules(QueryBuilderChildGroup $group, Builder $query): Builder
    {
        $boolean = $group->boolean();

        foreach ($group->children() as $child) {
            if ($child instanceof Filter) {
                $query = $this->applyQuery($query, $child, $boolean);
            }

            if ($child instanceof QueryBuilderChildGroup && $child->isNested()) {
                $query = $this->createNestedQuery($query, $child, $boolean);
            }
        }

        return $query;
    }

    /**
     * Start the creation of nested query.
     *
     * When a rule is actually a group of rules, we want to build a nested query with the specified condition (AND/OR)
     */
    protected function createNestedQuery(Builder $builder, QueryBuilderChildGroup $group, string $boolean): Builder
    {
        $boolean = $this->validateConditionBoolean($boolean);

        return $builder->where(function ($query) use ($group) {
            foreach ($group->children() as $child) {
                $method = 'applyQuery';

                if ($child instanceof QueryBuilderChildGroup) {
                    if ($child->isNested()) {
                        $method = 'createNestedQuery';
                    } else {
                        // Empty group?
                        continue;
                    }
                }

                $this->{$method}($query, $child, $group->boolean());
            }
        }, null, null, $boolean);
    }

    /**
     * Apply query for the given filter.
     */
    public function applyQuery(Builder $query, Filter|OperandFilter|RelationCountBasedFilter $filter, string $boolean): Builder
    {
        if (is_callable($filter->callback)) {
            $result = call_user_func_array($filter->callback, [$query, $boolean, $filter, $this, $filter->getValue()]);

            if ($result instanceof Builder) {
                $query = $result;
            }
        } elseif (method_exists($filter, 'apply')) {
            $result = $filter->apply($query, $boolean, $this);

            if ($result instanceof Builder) {
                $query = $result;
            }
        } elseif ($filter instanceof RelationCountBasedFilter && ! empty($filter->countableRelation())) {
            $query = $this->applyCountRelationshipQuery($query, $filter, $boolean);
        } elseif ($filter instanceof OperandFilter) {
            $query = $this->applyFilterOperatorQuery($query, $filter->getOperandInstance()->getFilter(), $boolean);
        } else {
            $query = $this->applyFilterOperatorQuery($query, $filter, $boolean);
        }

        if ($filter->tapCallback) {
            call_user_func_array($filter->tapCallback, [$query, $boolean, $filter, $this, $filter->getValue()]);
        }

        return $query;
    }

    /**
     * Apply query from the filter selected operator.
     */
    public function applyFilterOperatorQuery(
        Builder $query,
        Filter $filter,
        string $boolean,
        string|Expression|null $column = null
    ): Builder {
        return $this->applyOperatorQuery(
            $query,
            $boolean,
            $filter->getOperator(),
            $filter->getValue(),
            $column ?? $filter->getColumn($query)
        );
    }

    /**
     * Apply query for the given operator.
     */
    public function applyOperatorQuery(
        Builder $query,
        string $boolean,
        string $operator,
        mixed $value,
        string|Expression $column
    ): Builder {
        $method = $this->determineOperatorApplyMethod($operator);

        if (in_array($operator, $this->needsArray) && ! is_array($value)) {
            throw new QueryBuilderException(sprintf('The "%s" needs an array value.', $operator));
        }

        return $this->{$method}($query, $column, $boolean, $value);
    }

    /**
     * Apply query when the filter counts a relationship.
     *
     * @param  \Modules\Core\Filters\Filter&\Modules\Core\Filters\RelationCountBasedFilter  $filter
     */
    public function applyCountRelationshipQuery(
        Builder $query,
        $filter,
        string $boolean,
        ?Closure $callback = null
    ): Builder {
        return $query->has(
            $filter->countableRelation(),
            $this->getCountRelationshipSqlOperator($filter->getOperator()),
            $filter->getValue(),
            $boolean,
            $this->createCountRelationshipCallback($callback)
        );
    }

    /**
     * Create callback for the count relationship query.
     */
    protected function createCountRelationshipCallback(?Closure $queryCallback = null): callable
    {
        // We will provide a custom callback for counted relations and remove
        // any orderings and the global scope order from the "HasDisplayOrder" trait.
        // helps avoiding the error related to MySLQ group by
        // 1140 Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause
        return function (Builder $query) use ($queryCallback) {
            if (in_array(
                HasDisplayOrder::class,
                class_uses_recursive($query->getModel())
            )) {
                $query->withoutGlobalScope('displayOrder');
            }

            if ($queryCallback) {
                call_user_func_array($queryCallback, func_get_args());
            }

            $query->reorder();
        };
    }

    /**
     * Get the SQL operator when counting a relationship.
     */
    protected function getCountRelationshipSqlOperator(string $operator): string
    {
        return match ($operator) {
            'equal' => '=',
            'not_equal' => '!=',
            'less' => '<',
            'less_or_equal' => '<=',
            'greater' => '>',
            'greater_or_equal' => '>=',
            default => throw new Exception('Count relationship operator not supported.')
        };
    }

    /**
     * Determine the operator apply query method.
     */
    protected function determineOperatorApplyMethod(string $operator): string
    {
        return 'apply'.Str::studly($operator).'Query';
    }

    /**
     * Apply "begins with" operator query.
     */
    public function applyBeginsWithQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'LIKE', $value.'%', $boolean);
    }

    /**
     * Apply "not begins with" operator query.
     */
    public function applyNotBeginsWithQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'NOT LIKE', $value.'%', $boolean);
    }

    /**
     * Apply "between" operator query.
     */
    public function applyBetweenQuery(Builder $query, string|Expression $column, string $boolean, array|CarbonPeriod $value): Builder
    {
        if (is_array($value)) {
            $this->validateBetweenValue($value);
        }

        return $query->whereBetween($column, $value, $boolean);
    }

    /**
     * Apply "not between" operator query.
     */
    public function applyNotBetweenQuery(Builder $query, string|Expression $column, string $boolean, array|CarbonPeriod $value): Builder
    {
        if (is_array($value)) {
            $this->validateBetweenValue($value);
        }

        return $query->whereNotBetween($column, $value, $boolean);
    }

    /**
     * Validate between value.
     */
    protected function validateBetweenValue(array $value): void
    {
        if (count($value) < 2 || count($value) > 2) {
            throw new QueryBuilderException('The between value must contain only 2 items.');
        } elseif (count(array_filter($value)) < 2) {
            throw new QueryBuilderException('The between value contains empty items.');
        }
    }

    /**
     * Apply "contains" operator query.
     */
    public function applyContainsQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'LIKE', '%'.$value.'%', $boolean);
    }

    /**
     * Apply "not contains" operator query.
     */
    public function applyNotContainsQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'NOT LIKE', '%'.$value.'%', $boolean);
    }

    /**
     * Apply "ends with" operator query.
     */
    public function applyEndsWithQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'LIKE', '%'.$value, $boolean);
    }

    /**
     * Apply "not ends with" operator query.
     */
    public function applyNotEndsWithQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, 'NOT LIKE', '%'.$value, $boolean);
    }

    /**
     * Apply "equal" operator query.
     */
    public function applyEqualQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, '=', $value, $boolean);
    }

    /**
     * Apply "not equal" operator query.
     */
    public function applyNotEqualQuery(Builder $query, string|Expression $column, string $boolean, mixed $value): Builder
    {
        return $query->where($column, '!=', $value, $boolean);
    }

    /**
     * Apply "greater" operator query.
     */
    public function applyGreaterQuery(Builder $query, string|Expression $column, string $boolean, string|int|float $value): Builder
    {
        return $query->where($column, '>', $value, $boolean);
    }

    /**
     * Apply "greater or equal" operator query.
     */
    public function applyGreaterOrEqualQuery(Builder $query, string|Expression $column, string $boolean, string|int|float $value): Builder
    {
        return $query->where($column, '>=', $value, $boolean);
    }

    /**
     * Apply "in" operator query.
     */
    public function applyInQuery(Builder $query, string|Expression $column, string $boolean, array $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->whereIn($column, $value, $boolean);
    }

    /**
     * Apply "not in" operator query.
     */
    public function applyNotInQuery(Builder $query, string|Expression $column, string $boolean, array $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->whereNotIn($column, $value, $boolean);
    }

    /**
     * Apply "less" operator query.
     */
    public function applyLessQuery(Builder $query, string|Expression $column, string $boolean, string|int|float $value): Builder
    {
        return $query->where($column, '<', $value, $boolean);
    }

    /**
     * Apply "less or equal" operator query.
     */
    public function applyLessOrEqualQuery(Builder $query, string|Expression $column, string $boolean, string|int|float $value): Builder
    {
        return $query->where($column, '<=', $value, $boolean);
    }

    /**
     * Apply "is null" operator query.
     */
    public function applyIsNullQuery(Builder $query, string|Expression $column, string $boolean): Builder
    {
        return $query->whereNull($column, $boolean);
    }

    /**
     * Apply "is not null" operator query.
     */
    public function applyIsNotNullQuery(Builder $query, string|Expression $column, string $boolean): Builder
    {
        return $query->whereNotNull($column, $boolean);
    }

    /**
     * Validate the given condition boolean.
     *
     * Make sure that a condition is either 'or' or 'and'.
     */
    protected function validateConditionBoolean(string $boolean): string
    {
        $boolean = trim(strtolower($boolean));

        if ($boolean !== 'and' && $boolean !== 'or') {
            throw new QueryBuilderException("Condition can only be one of: 'and', 'or'.");
        }

        return $boolean;
    }
}
