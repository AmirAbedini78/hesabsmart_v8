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

use Illuminate\Database\Eloquent\Model;

class HasManyColumn extends RelationshipColumn
{
    /**
     * HasManyColumn is not sortable by default.
     */
    public bool $sortable = false;

    /**
     * Initialize new HasManyColumn instance class.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->fillRowDataUsing(function (array &$row, Model $model) {
            $row[$this->attribute] = $model->{$this->relationName}->map(function (Model $relation) {
                return $this->toRowData($relation);
            });
        });
    }
}
