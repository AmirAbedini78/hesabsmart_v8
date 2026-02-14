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

namespace Modules\Core\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;

/**
 * @mixin \Modules\Core\Resource\Resource
 */
trait ResolvesTables
{
    /**
     * Create new table instance.
     */
    protected function newTable(Builder $query, ResourceRequest $request, ?string $identifier = null): Table
    {
        // Perform count before any where (except authorizations related) queries are performed.
        $preTotal = with(clone $query)->count();

        return $this->table($query, $request, $identifier ?: $this->name())->setPreTotal($preTotal);
    }

    /**
     * Resolve the resource table class
     */
    public function resolveTable(ResourceRequest $request): Table
    {
        $table = $this->newTable($this->tableQuery($request), $request);

        $table->setColumns(
            $table->getColumns()->push(
                ...$this->columnsFromFields($this->fieldsForIndex())
            )
        );

        if ($table->resolveFilters($request)->isEmpty()) {
            $table->setFilters($this->resolveFilters($request));
        }

        if ($table->resolveActions($request)->isEmpty()) {
            $table->setActions($this->resolveActions($request));
        }

        return $table;
    }

    /**
     * Resolve the resource trashed table class
     */
    public function resolveTrashedTable(ResourceRequest $request): Table
    {
        $query = $this->tableQuery($request)->onlyTrashed();
        $table = $this->newTable($query, $request, $this->name().'-trashed');

        return $table->reorder($query->getModel()->getDeletedAtColumn())
            ->setColumns($this->columnsForTrashedTable($query))
            ->setActions($table->actionsForTrashed())
            ->withActionsColumn()
            ->withViews(false);
    }

    /**
     * Get the columns for trashed table.
     */
    public function columnsForTrashedTable(Builder $query): Collection
    {
        Column::$trashed = true;

        $deletedAtColumn = DateTime::make($query->getModel()->getDeletedAtColumn(), __('core::app.deleted_at'))->tapIndexColumn(
            fn (Column $column) => $column->width('250px')->minWidth('250px')
        );

        $columns = $this->columnsFromFields(
            $this->fieldsForIndex()->prepend($deletedAtColumn)->each->disableInlineEdit()
        )->each(function (Column $column) {
            $column->primary(false);
        });

        Column::$trashed = false;

        return $columns;
    }

    /**
     * Get the table columns from fields
     */
    public function columnsFromFields(Collection $fields): Collection
    {
        return $fields->map(function (Field $field) {
            return $field->resolveIndexColumn();
        })->filter();
    }
}
