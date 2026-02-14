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

namespace Modules\MailClient\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Users\Models\User;

class EmailAccountsForUserCriteria implements QueryCriteria
{
    /**
     * Initialize new EmailAccountsForUserCriteria instance.
     */
    public function __construct(protected ?User $user = null) {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $query->where(function ($query) {
            /** @var \Modules\Users\Models\User */
            $user = $this->user ?: Auth::user();

            $query->whereHas('user', function ($subQuery) use ($user) {
                $subQuery->where('user_id', $user->id);
            });

            if ($user->can('access shared inbox')) {
                $query->orDoesntHave('user');
            }
        });
    }
}
