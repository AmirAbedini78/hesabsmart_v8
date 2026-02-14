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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BelongsToColumn extends RelationshipColumn
{
    /**
     * Initialize new BelongsToColumn instance class.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->fillRowDataUsing(function (array &$row, Model $model) {
            $row[$this->attribute] = $this->toRowData($model->{$this->relationName});
        });
    }

    /**
     * Apply the order by query for the column.
     */
    public function orderBy(Builder $query, string $direction): Builder
    {
        $relation = $this->relationName;
        $instance = $query->getModel()->{$relation}();
        $table = $instance->getModel()->getTable();

        $alias = Str::snake(class_basename($query->getModel())).'_'.$relation.'_'.$table;

        $query->leftJoin(
            $table.' as '.$alias,
            $instance->getQualifiedForeignKeyName(),
            '=',
            $alias.'.id'
        );

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $direction, $alias, $this]);
        }

        return $query->orderBy(
            $alias.'.'.$this->relationField, $direction
        );
    }
}
