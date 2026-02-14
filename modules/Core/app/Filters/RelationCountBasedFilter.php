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

interface RelationCountBasedFilter
{
    /**
     * Compare the filter values from the given relation.
     */
    public function countFromRelation(?string $relationName = null): static;

    /**
     * Get the countable relation name for comparision.
     */
    public function countableRelation(): ?string;
}
