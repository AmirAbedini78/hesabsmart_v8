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

namespace Modules\MailClient\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\MailClient\Models\ScheduledEmail;
use Modules\Users\Models\User;

class ScheduledEmailPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the scheduled email.
     */
    public function delete(User $user, ScheduledEmail $message): bool
    {
        return (int) $user->id === (int) $message->user_id;
    }
}
