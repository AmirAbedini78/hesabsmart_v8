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

namespace Modules\Deals\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Users\Criteria\QueriesByUserCriteria;

class ViewAuthorizedDealsCriteria implements QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        /** @var \Modules\Users\Models\User */
        $user = Auth::user();

        if ($user->can('view all deals')) {
            return;
        }

        $query->where(function ($query) use ($user) {
            $query->criteria(new QueriesByUserCriteria($user));

            if ($user->can('view team deals')) {
                $query->orWhereHas('user.teams', function ($query) use ($user) {
                    $query->where('teams.user_id', $user->getKey());
                });
            }
        });
    }
}
