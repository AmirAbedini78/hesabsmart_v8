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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Collection;
use JsonSerializable;
use Modules\Core\Actions\Action;
use Modules\Core\Facades\Fields;
use Modules\Core\Http\Resources\DataViewResource;
use Modules\Core\Models\DataView;
use Modules\Users\Models\User;

class TableSettings implements Arrayable, JsonSerializable
{
    const MIN_POLLING_INTERVAL = 10;

    protected ?EloquentCollection $views = null;

    /**
     * Create new TableSettings instance.
     */
    public function __construct(protected Table $table, protected User $user)
    {
        if ($this->table->withViews) {
            $this->ensureUserHasAccessToAtLeastOneView();
        }
    }

    /**
     * Get the table available actions.
     *
     * The function removes also the actions that are hidden on INDEX
     */
    public function actions(): Collection
    {
        return $this->table->resolveActions($this->table->getRequest())
            ->filter(fn (Action $action) => $action->showInline || $action->showOnIndex)
            ->values();
    }

    /**
     * Get the actual table columns that should be displayed to the user.
     */
    public function getColumns(?int $viewId = null): Collection
    {
        $config = $viewId ? collect(
            $this->views()->find($viewId)?->config['table']['columns']
        ) : null;

        return $this->configureAuthorizedColumns()
            ->filter()
            ->unless(is_null($config), function (Collection $columns) use ($config) {
                $columns->each(function (Column $column) use ($config) {
                    // Set the hidden attribute becauase of the query.
                    $column->hidden(
                        $config->firstWhere('attribute', $column->attribute)['hidden'] ?? $column->hidden
                    );
                });
            })
            ->each(function (Column $column) {
                // We will add any customized attributes to the column field (if set)
                // so in case are used for inline edit, validation and other data is set correctly.
                if ($column->field) {
                    Fields::applyCustomizedAttributes(
                        $column->field, $this->table->identifier(), Fields::UPDATE_VIEW
                    );
                }
            })
            ->values();
    }

    /**
     * Get the available columns from the table and authorize.
     */
    protected function getAuthorizedColumns(): Collection
    {
        return $this->table->getColumns()->filter(
            fn (Column $column) => $column->authorizedToSee()
        )->values();
    }

    /**
     * Configure the table columns.
     */
    protected function configureAuthorizedColumns(): Collection
    {
        return $this->getAuthorizedColumns()
            ->each(function (Column $column, int $index) {
                if ($column instanceof ActionColumn) {
                    $column->order(1000)->hidden(false);
                } else {
                    $column->order($index + 1);
                }
            })
            ->sortBy('order')
            ->values()
            ->whenNotEmpty(function (Collection $columns) {
                // Ensure that the primary column is always first.
                $primaryIndex = $columns->search(fn (Column $column) => $column->isPrimary());

                if ($primaryIndex !== -1 && $primaryIndex !== 0) {
                    $primaryColumn = $columns->get($primaryIndex);
                    $columns->forget($primaryIndex)->prepend($primaryColumn->order(1));
                }

                return $columns;
            });
    }

    /**
     * Get the table available views.
     */
    public function views(): EloquentCollection
    {
        $result = $this->views ??= DataView::forUser($this->user->id, $this->table->identifier())->get();

        if ($this->table->isSingleViewOnly() && $result->count() > 1) {
            throw new MultipleRecordsFoundException($result->count());
        }

        return $result->map(function (DataView $view) {
            if (! isset($view->config['table'])) {
                $view->config['table'] = $this->createViewConfig();
                $view->save();
            }

            $view->config['table']['order'] = $this->ensureNotSortingByNotSortableColumns(
                $this->parseOrder($view->config['table']['order'])
            );

            $view->config['table']['pollingInterval'] = $this->parsePollingInterval(
                $view->config['table']['pollingInterval']
            );

            $view->config['table']['columns'] = $this->guardCustomizedColumns(
                $view->config['table']['columns']
            );

            return $view;
        });
    }

    /**
     * Create config for the view.
     */
    protected function createViewConfig(): array
    {
        return [
            ...$this->getDefaultSettings(),
            'columns' => $this->getColumns()
                ->filter(fn (Column $column) => ! $column instanceof ActionColumn)
                ->map(fn (Column $column) => [
                    'attribute' => $column->attribute,
                    'order' => $column->order,
                    'width' => $column->width,
                    'wrap' => $column->wrap,
                    'hidden' => $column->hidden,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Get the table default views.
     */
    protected function getDefaultViews(): array
    {
        return $this->table->defaultViews;
    }

    /**
     * Check if the table has default views defined.
     */
    protected function hasDefaultViews(): bool
    {
        return count($this->table->defaultViews) > 0;
    }

    /**
     * Get the currently stored default views.
     */
    protected function getExistingDefaultViews(): Collection
    {
        $query = DataView::ofIdentifier($this->table->identifier());

        // Using a regular where query, on one specific server "whereIn" was not working correctly, not sure why.
        $query->where(function (Builder $query) {
            foreach (array_keys($this->getDefaultViews()) as $i => $flag) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $query->$method('flag', $flag);
            }
        });

        return $query->get()->keyBy('flag');
    }

    /**
     * Ensure that the table default views are created.
     */
    protected function ensureDefaultViewsExists(): void
    {
        $existingViews = $this->getExistingDefaultViews();

        foreach ($this->getDefaultViews() as $flag => $attributes) {
            if (! isset($existingViews[$flag])) {
                $this->createView([
                    'flag' => $flag,
                    'name' => $attributes['name'],
                    'rules' => $attributes['rules'],
                    'config' => array_merge($attributes['config'] ?? [], ['table' => $this->createViewConfig()]),
                ]);
            }
        }
    }

    /**
     * Ensure the table has at least one view for the current user.
     */
    protected function ensureUserHasAccessToAtLeastOneView(): void
    {
        $identifier = $this->table->identifier();
        $userId = $this->user->id;

        if ($this->table->isSingleViewPerUser()) {
            if (DataView::ofIdentifier($identifier)->where('user_id', $userId)->doesntExist()) {
                $this->createView(['is_single' => true, 'user_id' => $userId]);
            }
        } elseif ($this->table->isSingleView()) {
            if (DataView::ofIdentifier($identifier)->doesntExist()) {
                $this->createView(['is_single' => true]);
            }
        }

        if ($this->hasDefaultViews()) {
            $this->ensureDefaultViewsExists();
        }

        if (DataView::forUser($userId, $identifier)->doesntExist()) {
            $this->createView(['user_id' => $userId]);
        }
    }

    /**
     * Create table data view.
     */
    protected function createView(array $attributes = []): void
    {
        DataView::unguarded(fn () => DataView::create(array_merge([
            'identifier' => $this->table->identifier(),
            'is_shared' => false,
            'is_single' => false,
            'user_id' => null,
            'name' => 'default',
            'config' => ['table' => $this->createViewConfig()],
        ], $attributes)));
    }

    /**
     * Ensure correct table polling interval.
     */
    protected function parsePollingInterval($interval): ?int
    {
        if (! is_null($interval)) {
            $interval = $interval < static::MIN_POLLING_INTERVAL ?
                static::MIN_POLLING_INTERVAL :
                $interval;
        }

        return $interval;
    }

    /**
     * Ensure correct table order.
     */
    protected function parseOrder(array $order): array
    {
        // Check and unset the custom ordered field in case no longer exists as available columns
        // For example it can happen a database change and this column is no longer available,
        // for this reason we must not sort by this column because it may be removed from database
        return collect($order)->filter(function ($data) {
            return $this->table->columnExist($data['attribute']);
        })->values()->all();
    }

    /**
     * Guard the customized columns config.
     */
    protected function guardCustomizedColumns(array $columns): array
    {
        foreach ($columns as $key => $column) {
            $instance = $this->table->getColumn($column['attribute']);

            if (! $instance) {
                continue;
            }

            if ($instance->isPrimary() || ! $instance->allowsVisibilityToggle()) {
                $columns[$key]['hidden'] = $instance->isHidden();
            }

            if (! $instance->allowsResizing()) {
                $columns[$key]['width'] = $instance->width;
            }

            if (! $this->table->allowsColumnsReorder()) {
                $column[$key]['order'] = $instance->order;
            }
        }

        return $columns;
    }

    /**
     * Ensure that the user is not sorting by not sortable columns.
     */
    protected function ensureNotSortingByNotSortableColumns(array $order): array
    {
        foreach ($order as $key => $sort) {
            $column = $this->table->getColumn($sort['attribute']);

            if (! $column->isSortable()) {
                unset($order[$key]);
            }
        }

        return $order;
    }

    /**
     * Get the table default settings.
     */
    public function getDefaultSettings(): array
    {
        return [
            'perPage' => $this->table->perPage,
            'maxHeight' => $this->table->maxHeight,
            'condensed' => $this->table->condensed,
            'bordered' => $this->table->bordered,
            'pollingInterval' => $this->table->pollingInterval,
            'order' => $this->parseOrder($this->table->getOrder()),
        ];
    }

    /**
     * toArray
     */
    public function toArray(): array
    {
        return array_merge([
            'identifier' => $this->table->identifier(),
            'allowDefaultSortChange' => $this->table->allowDefaultSortChange,
            'requestQueryString' => $this->table->getRequestQueryString(),
            'withViews' => $this->table->withViews,
            'reorderable' => $this->table->allowsColumnsReorder(),
            'rules' => $this->table->resolveFilters($this->table->getRequest()),
            'minimumPollingInterval' => static::MIN_POLLING_INTERVAL,
            'columns' => $this->getColumns(),
            'actions' => $this->actions(),
            'defaults' => $this->getDefaultSettings(),
        ], $this->table->withViews ? [
            'views' => DataViewResource::collection($this->views()),
            'singleViewOnly' => $this->table->isSingleViewOnly(),
        ] : []);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
