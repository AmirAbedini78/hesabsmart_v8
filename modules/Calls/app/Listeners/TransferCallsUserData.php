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

namespace Modules\Calls\Listeners;

use Modules\Calls\Models\Call;
use Modules\Users\Events\TransferringUserData;

class TransferCallsUserData
{
    /**
     * Handle the event.
     */
    public function handle(TransferringUserData $event): void
    {
        Call::where('user_id', $event->fromUserId)->update(['user_id' => $event->toUserId]);
    }
}
