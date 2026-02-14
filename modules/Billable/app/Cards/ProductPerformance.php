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

namespace Modules\Billable\Cards;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Billable\Models\Product;
use Modules\Core\Card\TableAsyncCard;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Models\Deal;
use Modules\Documents\Criteria\ViewAuthorizedDocumentsCriteria;
use Modules\Users\Criteria\ManagesOwnerTeamCriteria;
use Modules\Users\Criteria\QueriesByUserCriteria;

class ProductPerformance extends TableAsyncCard
{
    /**
     * Default sort field
     */
    protected Expression|string|null $sortBy = 'sold_count';

    /**
     * Default sort direction
     */
    protected string $sortDirection = 'desc';

    /**
     * Provide the query that will be used to retrieve the items.
     */
    public function query(Request $request): Builder
    {
        /** @var \Modules\Users\Models\User */
        $user = Auth::user();

        $query = (new Product)->newQuery()
            ->when($user->cant('view all products'), function ($query) use ($user) {
                if ($user->can('view team products')) {
                    $query->criteria(new ManagesOwnerTeamCriteria($user, 'creator'));
                } else {
                    $query->criteria(new QueriesByUserCriteria($user, 'created_by'));
                }
            });

        return $query
            ->withCount([
                'billables as sold_count' => $this->soldQueryCallback(),
                'billables as interest_count' => $this->interestInQueryProductCallback(),
            ])
            ->withSum([
                'billables as sold_sum_amount' => $this->soldQueryCallback(),
            ], 'amount_tax_exl');
    }

    /**
     * Get the searchable columns.
     */
    protected function getSearchableColumns(): array
    {
        return ['name' => 'like', 'sku'];
    }

    /**
     * Provide the table fields
     */
    public function fields(): array
    {
        return [
            [
                'key' => 'name',
                'label' => __('billable::product.name'),
                'sortable' => true,
            ],
            [
                'key' => 'interest_count',
                'label' => __('billable::product.interest_in_product'),
                'sortable' => true,
            ],
            [
                'key' => 'sold_count',
                'label' => __('billable::product.total_sold'),
                'sortable' => true,
            ],
            [
                'key' => 'sold_sum_amount',
                'label' => __('billable::product.sold_amount_exc_tax'),
                'sortable' => true,
                'format' => function ($model) {
                    return to_money($model->sold_sum_amount ?: 0)->format();
                },
            ],
        ];
    }

    /**
     * Get the interest in product query callback
     */
    protected function interestInQueryProductCallback(): callable
    {
        return function (Builder $query) {
            $query->select(DB::raw('COUNT(DISTINCT product_id) as interest_count'));

            return $query->whereHas('billable.billableable', function ($query) {
                return $query->where(fn (Builder $query) => $query->criteria(ViewAuthorizedDealsCriteria::class))
                    ->orWhere(fn (Builder $query) => $query->criteria(ViewAuthorizedDocumentsCriteria::class));
            });
        };
    }

    /**
     * Get the sold query callback
     */
    protected function soldQueryCallback(): callable
    {
        return function (Builder $query) {
            return $query->whereHas('billable', function ($query) {
                $query->where('billableable_type', Deal::class);

                return $query->whereHas('billableable', function ($query) {
                    $query->criteria(ViewAuthorizedDealsCriteria::class)->where('status', DealStatus::won);
                });
            });
        };
    }

    /**
     * The card name
     */
    public function name(): string
    {
        return __('billable::product.cards.performance');
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'helpText' => __('billable::product.cards.performance_info'),
        ]);
    }
}
