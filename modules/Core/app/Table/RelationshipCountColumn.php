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
use Illuminate\Support\Str;

class RelationshipCountColumn extends Column
{
    /**
     * The relationshiop name to perform count to.
     */
    public string $relationshipName;

    /**
     * Initialize new RelationshipCountColumn instance.
     */
    public function __construct(string $name, ?string $label = null, ?string $attribute = null)
    {
        parent::__construct($attribute ?: Str::snake($name).'_count', $label);

        $this->relationshipName = $name;

        $this->centered();
    }

    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, string $direction): Builder
    {
        return $query->orderBy($this->attribute, $direction);
    }
}
