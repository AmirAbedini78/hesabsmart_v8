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

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\Dashboard;
use Modules\Users\Models\User;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dashboard.
     */
    public function view(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can update the dashboards.
     */
    public function update(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can delete the dashboard.
     */
    public function delete(User $user, Dashboard $dashboard): bool
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }
}
