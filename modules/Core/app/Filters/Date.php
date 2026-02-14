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
use Illuminate\Support\Carbon;
use Modules\Core\Contracts\Filters\DisplaysInQuickFilter;

class Date extends Filter implements DisplaysInQuickFilter
{
    protected array $excludedIsOperatorOptions = [];

    /**
     * Apply the filter to the given query.
     */
    public function apply(Builder $query, string $boolean, QueryBuilder $builder): Builder
    {
        $value = $this->getValue();

        if ($this->getOperator() === 'is') {
            if ($value === 'past') {
                return $builder->applyLessQuery($query, $this->getColumn($query), $boolean, now());
            } elseif ($value === 'future') {
                return $builder->applyGreaterQuery($query, $this->getColumn($query), $boolean, now());
            }

            return $query->whereDateRange($this->getColumn($query), $value, $boolean);
        }

        return $builder->applyFilterOperatorQuery($query, $this, $boolean);
    }

    /**
     * Prepare the value for query.
     *
     * @return Carbon|array<Carbon>|string
     */
    public function prepareValue(string|array $value)
    {
        $operator = $this->getOperator();

        if ($operator === 'is') {
            return $value;
        }

        $value = $this->valueToCarbon($value);

        // When querying dates by equal or not equal we must provide a proper format
        // https://stackoverflow.com/questions/1754411/how-to-select-date-from-datetime-column
        // Because with Carbon, will search like e.q. where date = Y-m-d 00:00:00
        if (in_array($operator, ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal'])) {
            $value = $value->format('Y-m-d');
        }

        return $value;
    }

    /**
     * Convert the query date value to Carbon instance.
     *
     * @return Carbon|array<Carbon>
     */
    protected function valueToCarbon(string|array $value)
    {
        // Is between
        if (is_array($value)) {
            return array_map(fn ($date) => Carbon::parse($date), $value);
        }

        return $this->valueToCarbon([$value])[0];
    }

    /**
     * Exclude is operator options.
     */
    public function withoutIsOperatorOptions(string|array $options): static
    {
        $this->excludedIsOperatorOptions = array_fill_keys((array) $options, true);

        return $this;
    }

    /**
     * The IS operator options
     */
    public function isOperatorOptions(): array
    {
        return array_diff_key([
            'past' => __('core::dates.in_past'),
            'future' => __('core::dates.in_future'),
            'today' => __('core::dates.today'),
            'yesterday' => __('core::dates.yesterday'),
            'this_week' => __('core::dates.this_week'),
            'this_month' => __('core::dates.this_month'),
            'this_quarter' => __('core::dates.this_quarter'),
            'this_year' => __('core::dates.this_year'),
            'next_day' => __('core::dates.next_day'),
            'next_week' => __('core::dates.next_week'),
            'next_month' => __('core::dates.next_month'),
            'next_quarter' => __('core::dates.next_quarter'),
            'next_year' => __('core::dates.next_year'),
            'last_week' => __('core::dates.last_week'),
            'last_month' => __('core::dates.last_month'),
            'last_year' => __('core::dates.last_year'),
            'last_quarter' => __('core::dates.last_quarter'),
            'last_7_days' => __('core::dates.within.last_7_days'),
            'last_14_days' => __('core::dates.within.last_14_days'),
            'last_30_days' => __('core::dates.within.last_30_days'),
            'last_60_days' => __('core::dates.within.last_60_days'),
            'last_90_days' => __('core::dates.within.last_90_days'),
            'last_365_days' => __('core::dates.within.last_365_days'),
        ], $this->excludedIsOperatorOptions);
    }

    /**
     * Get the options to be used in quick filter.
     */
    public function getQuickFilterOptions(): array
    {
        return collect($this->isOperatorOptions())->map(fn ($label, $value) => [
            'value' => $value,
            'label' => $label,
        ])->values()->all();
    }

    /**
     * Get the quick filter operator.
     */
    public function getQuickFilterOperator(bool $multiple): string
    {
        return 'is';
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'date';
    }
}
