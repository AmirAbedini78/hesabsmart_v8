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

namespace Modules\MailClient\Synchronization\Exceptions;

class SyncFolderTimeoutException extends \RuntimeException
{
    /**
     * @param  string  $account  Account email
     * @param  string  $folderName  Email account folder full name
     */
    public function __construct($account, $folderName)
    {
        parent::__construct(
            sprintf(
                'Exit because of email account "%s" folder "%s" sync exceeded max save time per batch.',
                $account,
                $folderName
            )
        );
    }
}
