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

namespace Modules\Calls\Cards;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Modules\Calls\Models\Call;
use Modules\Core\Card\TableAsyncCard;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Users\Criteria\QueriesByUserCriteria;

class LoggedCalls extends TableAsyncCard
{
    /**
     * Indicates whether the table is searchable.
     */
    protected bool $searchable = false;

    /**
     * Default sort field.
     */
    protected Expression|string|null $sortBy = 'date';

    /**
     * The default renge/period selected.
     *
     * @var string
     */
    public string|int|null $defaultRange = 'this_month';

    /**
     * Provide the query that will be used to retrieve the items.
     */
    public function query(Request $request): Builder
    {
        $query = Call::with([
            'outcome' => fn (Builder $query) => $query->select(['id', 'name', 'swatch_color']),
            'user' => fn (Builder $query) => $query->select(['id', 'name']),
        ])->withAssociations();

        if ($userId = $this->getUserId($request)) {
            $query->criteria(new QueriesByUserCriteria($userId));
        }

        return $query->whereDateRange('date', $request->range ?? $this->defaultRange);
    }

    /**
     * Provide the table fields.
     */
    public function fields(): array
    {
        return [
            ['key' => 'date', 'label' => __('calls::call.call'), 'sortable' => true],
            ['key' => 'outcome.name', 'label' => __('calls::call.outcome.outcome'), 'sortable' => false, 'select' => false],
        ];
    }

    /**
     * Get the searchable columns.
     */
    protected function getSearchableColumns(): array
    {
        return ['body' => 'like', 'outcome.name'];
    }

    /**
     * Get the ranges available for the chart.
     */
    public function ranges(): array
    {
        return [
            'today' => __('core::dates.today'),
            'yesterday' => __('core::dates.yesterday'),
            'this_week' => __('core::dates.this_week'),
            'last_week' => __('core::dates.last_week'),
            'this_month' => __('core::dates.this_month'),
            'last_month' => __('core::dates.last_month'),
            'this_quarter' => __('core::dates.this_quarter'),
            'last_quarter' => __('core::dates.last_quarter'),
            'this_year' => __('core::dates.this_year'),
            'last_year' => __('core::dates.last_year'),
        ];
    }

    /**
     * Get the component name for the card.
     */
    public function component(): string
    {
        return 'logged-calls-card';
    }

    /**
     * The card name.
     */
    public function name(): string
    {
        return __('calls::call.cards.logged_calls');
    }

    /**
     * Get the columns that should be selected in the query.
     */
    protected function selectColumns(Request $request): array
    {
        return array_merge(
            parent::selectColumns($request),
            ['body', 'user_id', 'call_outcome_id']
        );
    }

    /**
     * Map the given model into a row.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function mapRow($model, Request $request)
    {
        return array_merge(
            parent::mapRow($model, $request),
            [
                'body' => clean($model->body),
                'user' => [
                    'name' => $model->user->name,
                ],
                'outcome' => [
                    'name' => $model->outcome->name,
                    'swatch_color' => $model->outcome->swatch_color,
                ],
                'associations' => $model->resource()->associateableResources()
                    ->mapWithKeys(function (Resource $resource, string $relation) use ($model) {
                        return [$resource->name() => $model->getRelationValue($relation)->map(function (Model $associated) use ($resource) {
                            return [
                                'id' => $associated->id,
                                'display_name' => $resource->titleFor($associated),
                                'path' => $resource->viewRouteFor($associated),
                            ];
                        })];
                    }),
            ]
        );
    }
}
