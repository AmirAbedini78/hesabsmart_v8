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

namespace Modules\Core\Table;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Actions\ForceDeleteAction;
use Modules\Core\Actions\ResolvesActions;
use Modules\Core\Actions\RestoreAction;
use Modules\Core\Criteria\FiltersCriteria;
use Modules\Core\Criteria\TableRequestCriteria;
use Modules\Core\Filters\FilterGroups;
use Modules\Core\Filters\QueryBuilderGroups;
use Modules\Core\Filters\ResolvesFilters;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Support\Makeable;

class Table
{
    use HandlesRelations,
        Makeable,
        ResolvesActions,
        ResolvesFilters,
        TransformsResult;

    /**
     * Additional database columns to select for the query.
     */
    protected array $select = [];

    /**
     * Additional attributes to be appended with the response.
     */
    protected array $appends = [];

    /**
     * Additional relations to eager load for the query.
     */
    protected array $with = [];

    /**
     * Table order.
     */
    public array $order = [];

    /**
     * Additional countable relations to eager load for the query.
     */
    protected array $withCount = [];

    /**
     * Custom table filters.
     */
    protected Collection|array $filters = [];

    /**
     * Custom table actions.
     */
    protected Collection|array $actions = [];

    /**
     * Additional request query string for the table request.
     */
    public array $requestQueryString = [];

    /**
     * Table default per page value.
     */
    public int $perPage = 25;

    /**
     * Table max height in pixels.
     */
    public int $maxHeight = 0;

    /**
     * Table default "condensed" value.
     */
    public bool $condensed = false;

    /**
     * Table default "bordered" value.
     */
    public bool $bordered = false;

    /**
     * Table default "pollingInterval" value.
     */
    public ?int $pollingInterval = null;

    /**
     * All time total count.
     */
    public ?int $preTotal = null;

    /**
     * Indicates whether the table has views.
     *
     * You must ensure all columns has unique ID's before setting this property to true.
     */
    public bool $withViews = false;

    /**
     * The default views for the table.
     */
    public array $defaultViews = [];

    /**
     * Indicates that the table has one single view for all users.
     */
    protected bool $singleView = false;

    /**
     * Indicates that the table has one single view per user.
     */
    protected bool $singleViewPerUser = false;

    /**
     * Indicates whether the user can reorder the columns when table has views.
     */
    protected bool $reorderable = true;

    /**
     * The active table view ID.
     */
    protected int $view;

    /**
     * Whether the table sorting options can be changed when the table has views.
     */
    public bool $allowDefaultSortChange = true;

    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = false;

    /**
     * Additional meta to include in the response.
     */
    public array $meta = [];

    /**
     * The query model instance.
     *
     * @var \Modules\Core\Models\Model
     */
    protected $model = null;

    /**
     * Table settings.
     */
    protected TableSettings $settings;

    /**
     * Columns collection.
     */
    protected Collection $columns;

    /**
     * @var null|callable
     */
    protected $provideRowClassUsing = null;

    /**
     * @var null|callable|string
     */
    protected $rowBorderVariant = null;

    /**
     * Initialize new Table instance.
     */
    public function __construct(protected Builder $query, protected ResourceRequest $request, protected string $identifier)
    {
        $this->model = $query->getModel();

        $this->setColumns($this->columns());
    }

    /**
     * Provide the table columns.
     */
    public function columns(): array
    {
        return [];
    }

    /**
     * Set the table available columns.
     */
    public function setColumns(array|Collection $columns): static
    {
        $this->columns = is_array($columns) ? new Collection($columns) : $columns;

        // Check if we need to add the action column
        if ($this->withActionsColumn === true && ! $this->hasActionsColumn()) {
            $this->addColumn(new ActionColumn);
        }

        return $this;
    }

    /**
     * Add new column to the table.
     */
    public function addColumn(Column $column): static
    {
        $this->columns->push($column);

        return $this;
    }

    /**
     * Check whether the table has actions column.
     */
    protected function hasActionsColumn(): bool
    {
        return $this->columns->whereInstanceOf(ActionColumn::class)->isNotEmpty();
    }

    /**
     * Get the table result.
     */
    public function result(): LengthAwarePaginator
    {
        return $this->transformResult(
            $this->getQuery()->paginate($this->request->perPage(default: $this->perPage))
        );
    }

    /**
     * Get the query intended for the table records.
     */
    public function getQuery(): Builder
    {
        // If you're combining withCount with a select statement,
        // ensure that you call withCount after the select method
        return $this->query->select($this->getColumnsToSelect())
            ->with($this->getWithRelationships())
            ->withCount($this->getCountedRelationships())
            ->criteria([$this->newRequestCriteria(), $this->createFiltersCriteria()]);
    }

    /**
     * Set the total before any where (except authorizations related) queries are performed.
     */
    public function setPreTotal(int $total): static
    {
        $this->preTotal = $total;

        return $this;
    }

    /**
     * Provide row class using a custom callback.
     */
    public function provideRowClassUsing(callable $callback): static
    {
        $this->provideRowClassUsing = $callback;

        return $this;
    }

    /**
     * Provide row border variant.
     */
    public function rowBorderVariant(callable|string $callback): static
    {
        $this->rowBorderVariant = $callback;

        return $this;
    }

    /**
     * Get the table request instance.
     */
    public function getRequest(): ResourceRequest
    {
        return $this->request;
    }

    /**
     * Get the server for the table AJAX request params.
     */
    public function getRequestQueryString(): array
    {
        return $this->requestQueryString;
    }

    /**
     * Set table default order by.
     */
    public function orderBy(string $attribute, string $dir = 'asc'): static
    {
        $this->order[] = ['attribute' => $attribute, 'direction' => $dir];

        return $this;
    }

    /**
     * Remove all existing orders and optionally add a new order.
     */
    public function reorder(?string $attribute = null, string $dir = 'asc'): static
    {
        $this->order = [];

        if ($attribute) {
            $this->orderBy($attribute, $dir);
        }

        return $this;
    }

    /**
     * Get the table default order.
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * Add additional relations to eager load.
     */
    public function with(string|array $relations): static
    {
        $this->with = array_merge($this->with, (array) $relations);

        return $this;
    }

    /**
     * Add additional countable relations to eager load.
     */
    public function withCount(string|array $relations): static
    {
        $this->withCount = array_merge($this->withCount, (array) $relations);

        return $this;
    }

    /**
     * Get the table available table filters.
     */
    public function filters(ResourceRequest $request): array|Collection
    {
        return $this->filters;
    }

    /**
     * Set table available filters.
     */
    public function setFilters(array|Collection $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Available table actions.
     */
    public function actions(ResourceRequest $request): array|Collection
    {
        return $this->actions;
    }

    /**
     * Set the table available actions.
     */
    public function setActions(array|Collection $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Indicates whether the table is with actions column.
     */
    public function withActionsColumn(): static
    {
        $this->withActionsColumn = true;

        if (! $this->hasActionsColumn()) {
            $this->addColumn(new ActionColumn);
        }

        return $this;
    }

    /**
     * Remove the action column from the table.
     */
    public function withoutActionsColumn(): static
    {
        $this->withActionsColumn = false;

        $this->setColumns(
            $this->columns->reject(fn (Column $column) => $column instanceof ActionColumn)->values()
        );

        return $this;
    }

    /**
     * Check whether a column exist by the given attribute.
     */
    public function columnExist(string $attribute): bool
    {
        return ! is_null($this->getColumn($attribute));
    }

    /**
     * Get defined column by given attribute.
     */
    public function getColumn(string $attribute): ?Column
    {
        return $this->columns->firstWhere('attribute', $attribute);
    }

    /**
     * Get all of the table available columns.
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * Check if the table is sorted by specific column.
     */
    public function isSortingByColumn(Column $column): bool
    {
        $sortingBy = $this->request->get('order', []);
        $sortedByFields = data_get($sortingBy, '*.attribute');

        return in_array($column->attribute, $sortedByFields);
    }

    /**
     * Get the table settings for the current request.
     */
    public function settings(): TableSettings
    {
        return $this->settings ??= new TableSettings($this, $this->request->user());
    }

    /**
     * Get the table identifier.
     */
    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set that the table has views.
     */
    public function withViews(bool $value = true): static
    {
        $this->withViews = $value;

        return $this;
    }

    /**
     * Set that the table has one single view for all users.
     */
    public function singleView(): static
    {
        $this->singleView = true;

        return $this;
    }

    /**
     * Check whether the table has single view only.
     */
    public function isSingleView(): bool
    {
        return $this->singleView;
    }

    /**
     * Set that the table has one single view per user.
     */
    public function singleViewPerUser(): static
    {
        $this->singleViewPerUser = true;

        return $this;
    }

    /**
     * Check whether the table has single view per user.
     */
    public function isSingleViewPerUser(): bool
    {
        return $this->singleViewPerUser;
    }

    /**
     * Check whether the table is single view only.
     */
    public function isSingleViewOnly(): bool
    {
        return $this->isSingleView() || $this->isSingleViewPerUser();
    }

    /**
     * Add table default view.
     */
    public function withDefaultView(string $name, string $flag, array|FilterGroups $rules = [], array $config = []): static
    {
        $this->defaultViews[$flag] = [
            'name' => $name,
            'rules' => $rules instanceof FilterGroups ? $rules->toArray() : $rules,
            'flag' => $flag,
            'config' => $config,
        ];

        return $this;
    }

    /**
     * Add additional database columns to select.
     */
    public function select(string|array $columns): static
    {
        $this->select = array_merge($this->select, (array) $columns);

        return $this;
    }

    /**
     * Get the actions when the table is intended to be displayed on the trashed view.
     *
     * NOTE: No authorization is performed on these action, all actions will be visible to the user
     */
    public function actionsForTrashed(): array
    {
        return [RestoreAction::make()->showInline(), ForceDeleteAction::make()->showInline()];
    }

    /**
     * Disable table ability the user to re-order columns when table has views.
     */
    public function doNotAllowReorder(): static
    {
        $this->reorderable = false;

        return $this;
    }

    /**
     * Check whether the user can reorder the columns.
     */
    public function allowsColumnsReorder(): bool
    {
        return $this->reorderable;
    }

    /**
     * Add attributes that should be appended in the response.
     */
    public function appends(string|array $attributes): static
    {
        $this->appends = array_merge($this->appends, (array) $attributes);

        return $this;
    }

    /**
     * Get column to select for the table query.
     *
     * Will return that columns only that are needed for the table,
     * For example of the user made some columns not visible they won't be queried.
     */
    protected function getColumnsToSelect(): array
    {
        $columns = $this->getQueryableUserColumns();

        $select = [];

        foreach ($columns as $column) {
            if (! $column->isRelation()) {
                if ($field = $this->getSelectableField($column)) {
                    $select[] = $field;
                }

                $select = array_merge($select, $this->qualifyColumn($column->select));

            } elseif ($column instanceof BelongsToColumn) {
                // Select the foreign key name for the BelongsToColumn
                // If not selected, the relation won't be queried properly
                $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
            }
        }

        if ($this->model->usesSoftDeletes()) {
            $select[] = $this->model->getQualifiedDeletedAtColumn();
        }

        return array_unique(array_merge(
            $this->qualifyColumn($this->select),
            [$this->model->getQualifiedKeyName().' as '.$this->model->getKeyName()],
            $select
        ), SORT_REGULAR);
    }

    /**
     * Prepare the searchable columns for the model from the table defined columns.
     */
    protected function prepareSearchableColumns(): array
    {
        return $this->getSearchableColumns()->mapWithKeys(function (Column|RelationshipColumn $column) {
            if ($column->isRelation()) {
                $searchableField = $column->relationName.'.'.$column->relationField;
            } else {
                $searchableField = $column->attribute;
            }

            return [$searchableField => 'like'];
        })->all();
    }

    /**
     * Filter the searchable columns.
     */
    protected function getSearchableColumns(): Collection
    {
        return $this->getUserColumns()->filter(function (Column $column) {
            // We will check if the column is date column, as date columns are not searchable
            // as there won't be accurate results because the database dates are stored in UTC timezone
            // In this case, the filters must be used
            // Additionally we will check if is countable column and the column counts
            if ($column instanceof DateTimeColumn ||
                $column instanceof DateColumn ||
                $column instanceof ActionColumn ||
                $column instanceof RelationshipCountColumn) {
                return false;
            }

            // Relation columns with no custom query are searchable
            if ($column->isRelation()) {
                return empty($column->queryAs);
            }

            // Regular database, and also is not queried
            // with DB::raw, when querying with DB::raw, you must implement
            // custom searching criteria
            return empty($column->queryAs);
        });
    }

    /**
     * Create new TableRequestCriteria criteria instance.
     */
    protected function newRequestCriteria(): TableRequestCriteria
    {
        return (new TableRequestCriteria($this))->setSearchFields($this->prepareSearchableColumns());
    }

    /**
     * Create new filters criteria for the table.
     */
    protected function createFiltersCriteria(): FiltersCriteria
    {
        return new FiltersCriteria(
            new QueryBuilderGroups($this->request->get('filters', []), $this->resolveFilters($this->request)),
            $this->request
        );
    }

    /**
     * Get field by column that should be included in the table select query.
     */
    protected function getSelectableField(Column|RelationshipColumn $column): mixed
    {
        if ($column instanceof ActionColumn || $column instanceof RelationshipCountColumn) {
            return null;
        }

        if (! empty($column->queryAs)) {
            return $column->queryAs;
        } elseif ($column instanceof RelationshipColumn) {
            return $this->qualifyColumn($column->relationField, $column->relationName);
        }

        return $this->qualifyColumn($column->attribute);
    }

    /**
     * Qualify the given column.
     */
    protected function qualifyColumn(string|array $column, ?string $forRelationship = null): array|string
    {
        if (is_array($column)) {
            return array_map(fn ($column) => $this->qualifyColumn($column, $forRelationship), $column);
        }

        if ($forRelationship) {
            return $this->model->{$forRelationship}()->qualifyColumn($column);
        }

        return $this->model->qualifyColumn($column);
    }

    /**
     * Get the columns for the table intended to be shown to the current user.
     */
    protected function getUserColumns(): Collection
    {
        return $this->settings()->getColumns(
            $this->request->integer('view') ?: null
        );
    }

    /**
     * Get the user columns that are included in query.
     */
    protected function getQueryableUserColumns(): Collection
    {
        return $this->getUserColumns()->filter(function (Column $column) {
            return $column->shouldQuery();
        })->values();
    }
}
