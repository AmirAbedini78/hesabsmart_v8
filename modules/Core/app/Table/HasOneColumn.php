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

class HasOneColumn extends RelationshipColumn
{
    /**
     * Initialize new HasOneColumn instance class.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->fillRowDataUsing(function (array &$row, Model $model) {
            $row[$this->attribute] = $this->toRowData($model->{$this->relationName});
        });
    }

    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, string $direction): Builder
    {
        $relationInstance = $query->getModel()->{$this->relationName}();

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $direction, $this]);
        }

        $qualifiedRelationshipField = $relationInstance->qualifyColumn($this->relationField);

        return $query->orderBy(
            $relationInstance->getModel()->select($qualifiedRelationshipField)
                ->whereColumn($relationInstance->getQualifiedForeignKeyName(), $query->getModel()->getQualifiedKeyName())
                ->orderBy($qualifiedRelationshipField, $direction)
                ->limit(1),
            $direction
        );
    }
}
