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

namespace Modules\Activities\Cards;

use Illuminate\Http\Request;
use Modules\Activities\Models\Activity;
use Modules\Core\Charts\Presentation;
use Modules\Users\Models\User;

class ActivitiesCreatedBySaleAgent extends Presentation
{
    /**
     * Axis Y Offset
     */
    public int $axisYOffset = 150;

    /**
     * Indicates whether the cart is horizontal
     */
    public bool $horizontal = true;

    /**
     * The default renge/period selected
     *
     * @var int
     */
    public string|int|null $defaultRange = 30;

    /**
     * Calculates activities created by sales agent
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->byDays('created_at')->count($request, Activity::class, 'created_by')
            ->label(function ($value) {
                return $this->users()->find($value)->name;
            });
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
            60 => __('core::dates.periods.60_days'),
            90 => __('core::dates.periods.90_days'),
            365 => __('core::dates.periods.365_days'),
        ];
    }

    /**
     * Get the all available users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function users()
    {
        return once(function () {
            return User::select(['id', 'name'])->get();
        });
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('activities::activity.cards.created_by_agent');
    }
}
