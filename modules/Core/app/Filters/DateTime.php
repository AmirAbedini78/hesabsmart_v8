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

use Illuminate\Support\Carbon;

class DateTime extends Date
{
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
        if (in_array($operator, ['equal', 'not_equal'])) {
            // We will change the operator on runtime so it uses between query.
            $this->setOperator($operator === 'equal' ? 'between' : 'not_between');

            return [$value, $value->copy()->addSeconds((24 * 60 * 60) - 1)];
        } elseif (in_array($operator, ['less', 'less_or_equal', 'greater', 'greater_or_equal'])) {
            // Check if the operator is neither 'less' nor 'greater_or_equal'
            if ($operator !== 'less' && $operator !== 'greater_or_equal') {
                // Add seconds to set the time to 23:59:59 on the same day
                $value->addSeconds((24 * 60 * 60) - 1);
            }
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
            return array_map(
                fn ($date) => Carbon::fromCurrentToAppTimezone($date), $value
            );
        }

        return $this->valueToCarbon([$value])[0];
    }
}
