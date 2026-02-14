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

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Resources\Resourceable;
use Modules\Core\Models\Model;
use Modules\Core\Support\GateHelper;

/** @mixin \Modules\Core\Table\Table */
trait TransformsResult
{
    /**
     * Transform the given result to response.
     */
    protected function transformResult(LengthAwarePaginator $result): LengthAwarePaginator
    {
        $columns = $this->getQueryableUserColumns();

        $result->getCollection()->transform(
            fn (Model $model) => $this->createRow($model, $columns)
        );

        $this->tapResult($result);

        return $result;
    }

    /**
     * Create new row for the response.
     */
    protected function createRow(Model $model, Collection $columns): array
    {
        $row = ['id' => $model->getKey()];

        foreach ($columns as $column) {
            $this->processColumnRow($model, $column, $row);
        }

        return $row;
    }

    /**
     * Process each column and update the row data accordingly.
     */
    protected function processColumnRow(Model $model, Column $column, array &$row): void
    {
        $this->addModelAttributesAndAuthorizationsToRow($model, $column, $row);
        $this->addColumnDataAndRelationshipsToRow($model, $column, $row);
        $this->addRowAttributes($model, $row);
        $this->addDisabledFieldsForEditToRow($model, $column, $row);

        if ($model->usesSoftDeletes()) {
            $row['_trashed'] = $model->trashed();
        }
    }

    /**
     * Append model attributes and authorizations to the row.
     */
    protected function addModelAttributesAndAuthorizationsToRow(Model $model, Column $column, array &$row): void
    {
        $extra = $model->only(array_merge($this->appends, $column->appends));

        if ($model instanceof Resourceable) {
            $extra['path'] = $model->resource()->viewRouteFor($model);
        }

        $row = array_merge($row, $extra);

        $row['authorizations'] = GateHelper::authorizations($model);
    }

    /**
     * Append column data and counted relationships to the row.
     */
    protected function addColumnDataAndRelationshipsToRow(Model $model, Column $column, array &$row): void
    {
        $column->fillRowData($row, $model);
        $this->addCountedRelationshipsToRow($row, $model);
    }

    /**
     * Append the row related attributes to the row response.
     */
    protected function addRowAttributes(Model $model, array &$row): void
    {
        $caller = function ($func) use ($model, $row) {
            return call_user_func_array($func, [$row, $model]);
        };

        if (is_callable($this->provideRowClassUsing)) {
            $row['_row_class'] = $caller($this->provideRowClassUsing);
        }

        if ($this->rowBorderVariant !== null) {
            $row['_row_border'] = is_string($this->rowBorderVariant) ? $this->rowBorderVariant : $caller($this->rowBorderVariant);
        }
    }

    /**
     * Add the disabled fields data to the row.
     */
    protected function addDisabledFieldsForEditToRow(Model $model, Column $column, array &$row): void
    {
        $field = $column->field;

        if (! $field) {
            return;
        }

        if (! array_key_exists('_edit_disabled', $row)) {
            $row['_edit_disabled'] = [];
        }

        // When the field is not applicable for update and has custom inline edit field
        // we will assume that we wanted this field to be applicable for update as added custom inline edit field(s)
        $row['_edit_disabled'][$field->attribute] = with($field, function ($field) use ($model) {
            if (! $field->isApplicableForUpdate() && is_null($field->inlineEditField())) {
                return true;
            }

            return $field->isInlineEditDisabled($model);
        });
    }

    /**
     * Add the counted relationship to the row, including the counted without columns.
     */
    protected function addCountedRelationshipsToRow(array &$row, Model $model): void
    {
        foreach ($this->getCountedRelationships() as $key => $relation) {
            if (is_string($key)) {
                $relation = $key;
            } elseif (str_contains($relation, ' as ')) {
                $relation = Str::before($relation, ' as');
            }

            $attribute = $relation.'_count';

            if (! array_key_exists($attribute, $row)) {
                $row[$attribute] = $model->{$attribute};
            }
        }
    }

    /**
     * Tap the table response.
     */
    protected function tapResult(LengthAwarePaginator $response): void {}
}
