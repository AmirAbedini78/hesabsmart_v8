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
use Illuminate\Http\Request;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Core\Filters\QueryBuilder;
use Modules\Core\Filters\QueryBuilderGroups;

class FiltersCriteria implements QueryCriteria
{
    /**
     * Initialize new FiltersCriteria instance.
     */
    public function __construct(protected QueryBuilderGroups $groups, protected Request $request) {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $query->where(function (Builder $filterQuery) use ($query) {
            (new QueryBuilder($this->groups))->apply($filterQuery);

            // On the parent model query remove any global scopes
            // that are removed from the where for the current query instance
            // for example, soft deleted when calling onlyTrashed
            $query->withoutGlobalScopes($filterQuery->removedScopes());
        });
    }
}
