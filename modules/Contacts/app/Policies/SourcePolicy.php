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

namespace Modules\Contacts\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Contacts\Models\Source;
use Modules\Users\Models\User;

class SourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sources.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the source.
     */
    public function view(User $user, Source $source): bool
    {
        return true;
    }

    /**
     * Determine if the given user can create source.
     */
    public function create(User $user): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the source.
     */
    public function update(User $user, Source $source): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the source.
     */
    public function delete(User $user, Source $source): bool
    {
        // Only super admins
        return false;
    }
}
