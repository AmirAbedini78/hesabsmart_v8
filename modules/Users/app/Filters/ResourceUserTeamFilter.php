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

namespace Modules\Users\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Filters\QueryBuilder;
use Modules\Core\Filters\Select;
use Modules\Users\Models\Team;

class ResourceUserTeamFilter extends Select
{
    /**
     * Create new ResourceUserTeamFilter instance
     */
    public function __construct(string $label, string $userRelationship = 'user')
    {
        parent::__construct('team', $label);

        $this->valueKey('id')
            ->labelKey('name')
            ->options($this->teams(...))
            ->applyQueryUsing(
                function (Builder $query, string $boolean, ResourceUserTeamFilter $filter, QueryBuilder $builder) use ($userRelationship) {
                    return $query->whereHas(
                        $userRelationship.'.teams',
                        fn (Builder $query) => $builder->applyFilterOperatorQuery($query, $filter, $boolean, 'teams.id')
                    );
                }
            );
    }

    /**
     * Get the filter teams.
     */
    public function teams(): Collection
    {
        return Team::userTeams()->get(['id', 'name']);
    }
}
