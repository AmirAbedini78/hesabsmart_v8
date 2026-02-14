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
use Modules\MailClient\Models\PredefinedMailTemplate;
use Modules\Users\Models\User;

class PredefinedMailTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the note.
     */
    public function view(User $user, PredefinedMailTemplate $template): bool
    {
        return (int) $user->id === (int) $template->user_id;
    }

    /**
     * Determine whether the user can update the note.
     */
    public function update(User $user, PredefinedMailTemplate $template): bool
    {
        return (int) $user->id === (int) $template->user_id;
    }

    /**
     * Determine whether the user can delete the note.
     */
    public function delete(User $user, PredefinedMailTemplate $template): bool
    {
        return (int) $user->id === (int) $template->user_id;
    }
}
