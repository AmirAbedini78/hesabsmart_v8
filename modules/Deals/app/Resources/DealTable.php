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

namespace Modules\Deals\Resources;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Table\Table;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Stage;

class DealTable extends Table
{
    /**
     * Additional database columns to select for the table query.
     */
    protected array $select = [
        'user_id', // user_id is for the policy checks
        'expected_close_date', // falls_behind_expected_close_date check
        'status', // falls_behind_expected_close_date check
    ];

    /**
     * Attributes to be appended with the response.
     */
    protected array $appends = [
        'falls_behind_expected_close_date', // row class
    ];

    /**
     * Indicates whether the table has views.
     */
    public bool $withViews = true;

    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = true;

    /**
     * Tap the transformed result.
     */
    protected function tapResult(LengthAwarePaginator $response): void
    {
        $query = Deal::criteria([
            $this->newRequestCriteria(),  $this->createFiltersCriteria(), ViewAuthorizedDealsCriteria::class,
        ]);

        $summary = Stage::summary($query, $this->request->integer('pipeline_id') ?: null);

        $this->meta = ['summary' => [
            'count' => $summary->sum('count'),
            'value' => $summary->sum('value'),
            'weighted_value' => $summary->sum('weighted_value'),
        ]];
    }
}
