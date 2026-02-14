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
use Modules\Core\Models\Import;
use Modules\Users\Models\User;

class ImportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user is allowed to perform import.
     */
    public function import(User $user, Import $import)
    {
        return $this->isSameUser($user, $import);
    }

    /**
     * Determine whether the user can delete the import.
     */
    public function delete(User $user, Import $import): bool
    {
        return $this->isSameUser($user, $import);
    }

    /**
     * Determine whether the user can revert the import.
     */
    public function revert(User $user, Import $import): bool
    {
        return $this->isSameUser($user, $import);
    }

    /**
     * Determine whether the user can upload fixed skip file.
     */
    public function uploadFixedSkipFile(User $user, Import $import): bool
    {
        return $this->isSameUser($user, $import);
    }

    /**
     *Determine whether the user can upload fixed skip file
     */
    public function downloadSkipFile(User $user, Import $import): bool
    {
        return $this->isSameUser($user, $import);
    }

    protected function isSameUser(User $user, Import $import): bool
    {
        return (int) $import->user_id === (int) $user->id;
    }
}
