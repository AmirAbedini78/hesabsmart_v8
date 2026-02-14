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
use Modules\Core\Models\DataView;
use Modules\Users\Models\User;

class DataViewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the view.
     */
    public function update(User $user, DataView $view): bool
    {
        if ($view->isSharedFromAnotherUser($user)) {
            return false;
        }

        return (int) $view->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the view.
     */
    public function delete(User $user, DataView $view): bool
    {
        if ($view->isSharedFromAnotherUser($user)) {
            return false;
        }

        return (int) $view->user_id === (int) $user->id;
    }
}
