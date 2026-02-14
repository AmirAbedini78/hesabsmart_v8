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

use Modules\Core\Common\OAuth\Events\OAuthAccountDeleting;
use Modules\Core\Common\Synchronization\SyncState;
use Modules\MailClient\Models\EmailAccount;

class StopRelatedOAuthEmailAccounts
{
    /**
     * Stop the related email accounts of the OAuth account when deleting.
     */
    public function handle(OAuthAccountDeleting $event): void
    {
        $oAuthAccount = $event->account;

        $emailAccount = EmailAccount::where('access_token_id', $oAuthAccount->id)->first();

        if ($emailAccount) {
            $emailAccount->setSyncState(
                SyncState::STOPPED,
                'The connected OAuth account ('.$oAuthAccount->email.') was deleted, hence, working with this email account cannot be proceeded. Consider removing the email account from the application.'
            );

            $emailAccount->fill(['access_token_id' => null])->save();
        }
    }
}
