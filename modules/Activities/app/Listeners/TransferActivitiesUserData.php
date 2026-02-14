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

namespace Modules\Activities\Listeners;

use Modules\Activities\Models\Activity;
use Modules\Users\Events\TransferringUserData;

class TransferActivitiesUserData
{
    /**
     * Handle the event.
     */
    public function handle(TransferringUserData $event): void
    {
        $event->fromUser->guests()->delete();
        $event->fromUser->connectedCalendars->each->delete();

        Activity::withTrashed()->where('created_by', $event->fromUserId)->update(['created_by' => $event->toUserId]);
        Activity::withTrashed()->where('user_id', $event->fromUserId)->update(['user_id' => $event->toUserId]);
    }
}
