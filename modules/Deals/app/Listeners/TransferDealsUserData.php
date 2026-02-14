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

namespace Modules\Deals\Listeners;

use Modules\Deals\Models\Deal;
use Modules\Users\Events\TransferringUserData;

class TransferDealsUserData
{
    /**
     * Handle the event.
     */
    public function handle(TransferringUserData $event): void
    {
        Deal::withTrashed()->where('created_by', $event->fromUserId)->update(['created_by' => $event->toUserId]);
        Deal::withTrashed()->where('user_id', $event->fromUserId)->update(['user_id' => $event->toUserId]);
    }
}
