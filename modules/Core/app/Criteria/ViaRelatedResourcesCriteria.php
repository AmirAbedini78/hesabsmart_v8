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

namespace Modules\Core\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Contracts\Criteria\QueryCriteria;

class ViaRelatedResourcesCriteria implements QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $base): void
    {
        $base->where(function ($query) use ($base) {
            $resource = $base->getModel()->resource();

            $i = 0;
            foreach ($resource->associateableResources() as $relation => $resource) {
                if ($criteria = $resource->viewAuthorizedRecordsCriteria()) {
                    $query->{$i === 0 ? 'whereHas' : 'orWhereHas'}($relation, function ($query) use ($criteria) {
                        (new $criteria)->apply($query);
                    });
                }
                $i++;
            }

            if (method_exists($base, 'user')) {
                $query->orWhere($base->user()->getForeignKeyName(), auth()->id());
            }
        });
    }
}
