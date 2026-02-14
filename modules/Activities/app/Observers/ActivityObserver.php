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

namespace Modules\Activities\Observers;

use Modules\Activities\Models\Activity;
use Modules\Activities\Models\ActivityType;
use Modules\Users\Models\User;

class ActivityObserver
{
    /**
     * Handle the Activity "creating" event.
     */
    public function creating(Activity $activity): void
    {
        if (! $activity->end_date) {
            $activity->end_date = $activity->due_date;
        }

        if (! $activity->activity_type_id) {
            $activity->activity_type_id = ActivityType::getDefaultType();
        }

        $activity->reminder_at = Activity::determineReminderAtDate($activity);
    }

    /**
     * Handle the Activity "updating" event.
     */
    public function updating(Activity $activity): void
    {
        $activity::$preUpdateUser = $activity->isDirty('user_id') ?
            User::find($activity->getOriginal('user_id')) :
            User::find($activity->user_id);

        if ($activity->isDirty('reminder_minutes_before')) {
            $this->ensureReminderColumnsAreUpToDate($activity);
        }
    }

    /**
     * Handle the Activity "deleting" event.
     */
    public function deleting(Activity $activity): void
    {
        if ($activity->isForceDeleting()) {
            $activity->purge(fromCalendar: ! $activity->trashed());
        }
    }

    /**
     * Ensure that the remidner at and reminded_at columns are up to date.
     */
    protected function ensureReminderColumnsAreUpToDate(Activity $activity)
    {
        $originalReminderAt = $activity->reminder_at;

        $activity->reminder_at = Activity::determineReminderAtDate($activity);

        // We will check if the reminder_at column has been changed, if yes,
        // we will reset the reminded_at value to null so new reminder can be sent to the user
        if (is_null($activity->reminder_at) ||
            ($activity->is_reminded && $originalReminderAt->ne($activity->reminder_at))) {
            $activity->reminded_at = null;
        }
    }
}
