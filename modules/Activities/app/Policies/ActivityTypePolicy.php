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

namespace Modules\Activities\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Activities\Models\ActivityType;
use Modules\Users\Models\User;

class ActivityTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any types.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the type.
     */
    public function view(User $user, ActivityType $type): bool
    {
        return true;
    }

    /**
     * Determine if the given user can create type.
     */
    public function create(User $user): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the type.
     */
    public function update(User $user, ActivityType $type): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the type.
     */
    public function delete(User $user, ActivityType $type): bool
    {
        // Only super admins
        return false;
    }
}
