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

namespace Modules\Core\Macros;

use Closure;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class QueryDateRange
{
    /**
     * Apply where date range column query.
     */
    public function whereDateRange(): Closure
    {
        return function ($column, string $range, $boolean = 'and', $not = false) {
            /** @var \Illuminate\Database\Eloquent\Builder */
            $builder = $this;

            $tz = tz()->current();
            $now = Carbon::asCurrentTimezone();

            $period = match ($range) {
                'today' => [$now->copy()->startOfDay(), $now->endOfDay()],
                'yesterday' => [$now->copy()->yesterday($tz)->startOfDay(), $now->yesterday($tz)->endOfDay()],
                'next_day' => [$now->copy()->tomorrow($tz)->startOfDay(), $now->tomorrow($tz)->endOfDay()],
                'this_week' => [$now->copy()->startOfWeek(), $now->endOfWeek()],
                'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
                'next_week' => [$now->copy()->addWeek()->startOfWeek(), $now->addWeek()->endOfWeek()],
                'this_month' => [$now->copy()->startOfMonth(), $now->endOfMonth()],
                // https://github.com/briannesbitt/Carbon/issues/639
                'last_month' => [
                    $now->copy()->startOfMonth()->subMonth()->startOfMonth(),
                    $now->startOfMonth()->subMonth()->endOfMonth(),
                ],
                'next_month' => [$now->copy()->startOfMonth()->addMonth(), $now->startOfMonth()->addMonth()->endOfMonth()],
                'this_quarter' => [$now->copy()->startOfQuarter(), $now->endOfQuarter()],
                'last_quarter' => [$now->copy()->subQuarter()->startOfQuarter(), $now->subQuarter()->endOfQuarter()],
                'next_quarter' => [$now->copy()->addQuarter()->startOfQuarter(), $now->addQuarter()->endOfQuarter()],
                'this_year' => [$now->copy()->startOfYear(), $now->endOfYear()],
                'last_year' => [$now->copy()->subYear()->startOfYear(), $now->subYear()->endOfYear()],
                'next_year' => [$now->copy()->addYear()->startOfYear(), $now->addYear()->endOfYear()],
                'last_7_days' => [$now->copy()->subDays(7)->startOfDay(), $now->endOfDay()],
                'last_14_days' => [$now->copy()->subDays(14)->startOfDay(), $now->endOfDay()],
                'last_30_days' => [$now->copy()->subDays(30)->startOfDay(), $now->endOfDay()],
                'last_60_days' => [$now->copy()->subDays(60)->startOfDay(), $now->endOfDay()],
                'last_90_days' => [$now->copy()->subDays(90)->startOfDay(), $now->endOfDay()],
                'last_365_days' => [$now->copy()->subDays(365)->startOfDay(), $now->endOfDay()],
                default => throw new InvalidArgumentException('Invalid date range type.')
            };

            return $builder->whereBetween(
                $column, [$period[0]->inAppTimezone(), $period[1]->inAppTimezone()], $boolean, $not
            );
        };
    }
}
