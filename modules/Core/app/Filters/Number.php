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

namespace Modules\Core\Filters;

use Illuminate\Support\Str;

class Number extends Filter implements RelationCountBasedFilter
{
    /**
     * The relation that the count is performed on.
     */
    public ?string $countFromRelation = null;

    /**
     * Compare the filter values from the given relation.
     */
    public function countFromRelation(?string $relationName = null): static
    {
        $this->countFromRelation = $relationName ?? lcfirst(Str::studly($this->field()));

        $except = ['between', 'not_between', 'is_null', 'is_not_null'];

        $this->operators(
            array_values(
                array_filter($this->getOperators(), fn (string $operator) => ! in_array($operator, $except))
            )
        );

        return $this;
    }

    /**
     * Get the countable relation name for comparision.
     */
    public function countableRelation(): ?string
    {
        return $this->countFromRelation;
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'number';
    }
}
