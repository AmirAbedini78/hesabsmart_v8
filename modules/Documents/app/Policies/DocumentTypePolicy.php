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

namespace Modules\Documents\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Documents\Models\DocumentType;
use Modules\Users\Models\User;

class DocumentTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any types.
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the type.
     */
    public function view(User $user, DocumentType $type): bool
    {
        return $type->isVisible($user);
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
    public function update(User $user, DocumentType $type): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the type.
     */
    public function delete(User $user, DocumentType $type): bool
    {
        // Only super admins
        return false;
    }
}
