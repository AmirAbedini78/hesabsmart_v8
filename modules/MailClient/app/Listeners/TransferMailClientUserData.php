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

namespace Modules\MailClient\Listeners;

use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\PredefinedMailTemplate;
use Modules\Users\Events\TransferringUserData;

class TransferMailClientUserData
{
    /**
     * Handle the event.
     */
    public function handle(TransferringUserData $event): void
    {
        $this->emailAccounts($event->toUserId, $event->fromUserId);
        $this->predefinedMailTemplates($event->toUserId, $event->fromUserId);
    }

    /**
     * Transfer accounts created by.
     *
     * Personal accounts are deleted, here only shared are transfered.
     */
    public function emailAccounts($toUserId, $fromUserId): void
    {
        EmailAccount::where('created_by', $fromUserId)->update(['created_by' => $toUserId]);
    }

    /**
     * Transfer shared predefined mail templates.
     */
    public function predefinedMailTemplates($toUserId, $fromUserId): void
    {
        // Purge user non shared mail templates.
        PredefinedMailTemplate::where('user_id', $fromUserId)->where('is_shared', 0)->delete();

        // Transfer shared mail templates to the selected user.
        PredefinedMailTemplate::where('user_id', $fromUserId)
            ->shared()
            ->update(['user_id' => $toUserId]);
    }
}
