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

namespace Modules\Users\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Users\Models\User;

class ManagesOwnerTeamCriteria implements QueryCriteria
{
    /**
     * Initialize new ManagesOwnerTeamCriteria instance
     */
    public function __construct(protected User $user, protected string $relation = 'user') {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $model): void
    {
        $model->whereHas($this->relation.'.teams', function ($query) {
            $query->where('teams.user_id', $this->user->getKey());
        });
    }
}
