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

namespace Modules\Brands\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Brands\Models\Brand;
use Modules\Users\Models\User;

class BrandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the brand.
     */
    public function view(User $user, Brand $brand): bool
    {
        return $brand->isVisible($user);
    }

    /**
     * Determine if the given user can create brand.
     */
    public function create(User $user): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the brand.
     */
    public function update(User $user, Brand $brand): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the brand.
     */
    public function delete(User $user, Brand $brand): bool
    {
        // Only super admins
        return false;
    }
}
