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

namespace Modules\Deals\Menu;

use Modules\Core\Menu\Metric;
use Modules\Core\Models\DataView;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Models\Deal;

class OpenDealsMetric extends Metric
{
    /**
     * Get the metric name
     */
    public function name(): string
    {
        return __('deals::deal.metrics.open');
    }

    /**
     * Get the metric count
     */
    public function count(): int
    {
        return Deal::criteria(ViewAuthorizedDealsCriteria::class)->open()->count();
    }

    /**
     * Get the background color variant when the metric count is bigger then zero
     */
    public function backgroundColorVariant(): string
    {
        return 'info';
    }

    /**
     * Get the front-end route that the highly will redirect to
     */
    public function route(): array|string
    {
        $view = DataView::findByFlag('open-deals');

        return [
            'name' => 'deal-index',
            'query' => [
                'view_id' => $view?->id,
            ],
        ];
    }
}
