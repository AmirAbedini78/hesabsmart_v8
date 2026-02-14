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

namespace Modules\Activities\Menu;

use Modules\Activities\Criteria\ViewAuthorizedActivitiesCriteria;
use Modules\Activities\Models\Activity;
use Modules\Core\Menu\Metric;
use Modules\Core\Models\DataView;

class TodaysActivitiesMetric extends Metric
{
    /**
     * Get the metric name
     */
    public function name(): string
    {
        return __('activities::activity.metrics.todays');
    }

    /**
     * Get the metric count
     */
    public function count(): int
    {
        return Activity::dueToday()->criteria(ViewAuthorizedActivitiesCriteria::class)->count();
    }

    /**
     * Get the background color variant when the metric count is bigger then zero
     */
    public function backgroundColorVariant(): string
    {
        return 'warning';
    }

    /**
     * Get the front-end route that the highly will redirect to
     */
    public function route(): array|string
    {
        $view = DataView::findByFlag('due-today-activities');

        return [
            'name' => 'activity-index',
            'query' => [
                'view_id' => $view?->id,
            ],
        ];
    }
}
