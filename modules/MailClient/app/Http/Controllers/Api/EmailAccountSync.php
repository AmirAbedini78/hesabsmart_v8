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

namespace Modules\MailClient\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\MailClient\Http\Resources\EmailAccountResource;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Synchronization\Exceptions\SynchronizationInProgressException;

class EmailAccountSync extends ApiController
{
    /**
     * Invoke synchronization for the given email account.
     *
     * @throws \Modules\MailClient\Synchronization\Exceptions\SynchronizationInProgressException
     */
    public function __invoke(string $accountId): JsonResponse
    {
        $this->authorize('view', EmailAccount::findOrFail($accountId));

        $exitCode = Innoclapps::runCommand('mailclient:sync', [
            '--account' => $accountId,
            '--broadcast' => false,
            '--isolated' => 5,
        ]);

        if ($exitCode === 5) {
            throw new SynchronizationInProgressException;
        }

        return $this->response(
            new EmailAccountResource(
                EmailAccount::withCommon()->find($accountId)
            )
        );
    }
}
