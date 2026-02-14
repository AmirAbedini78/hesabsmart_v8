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

namespace Modules\Core\Criteria;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Core\Contracts\Criteria\QueryCriteria;

class ExportRequestCriteria implements QueryCriteria
{
    /**
     * Create new ExportRequestCriteria instance.
     */
    public function __construct(protected string|array|null $period, protected ?string $dateRangeColumn = null) {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $column = $this->determineDateRangeColumn($query);

        if ($this->period) {
            $this->applyRangeQuery($column, $query);
        }

        $query->orderByDesc($column);
    }

    /**
     * Apply the date range query.
     */
    protected function applyRangeQuery($column, Builder $query): void
    {
        // Is between with actual dates selected.
        if (is_array($this->period)) {
            $query->whereBetween($column, array_map(
                fn ($date) => Carbon::fromCurrentToAppTimezone($date), $this->period
            ));
        } else {
            // Is regular range.
            $query->whereDateRange($column, $this->period);
        }
    }

    /**
     * Determine the date range field attribute.
     */
    protected function determineDateRangeColumn($query): string
    {
        $dateRangeColumn = $this->dateRangeColumn;

        if (empty($dateRangeColumn)) {
            if (! $query->getModel()->usesTimestamps()) {
                throw new Exception('Exportable resource model must use timestamps.');
            }

            $dateRangeColumn = $query->getModel()->getCreatedAtColumn();
        }

        return $dateRangeColumn;
    }
}
