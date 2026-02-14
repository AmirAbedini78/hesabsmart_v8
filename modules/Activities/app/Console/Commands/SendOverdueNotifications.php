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

namespace Modules\Activities\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\Activities\Models\Activity;
use Modules\Activities\Notifications\ActivityReminder;
use Throwable;

class SendOverdueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies owners of due activities.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Activity::reminderable()
            ->with(['user', 'type'])
            ->get()
            ->each(function (Activity $activity) {
                $activity->calendarable = false;

                try {
                    $activity->user->notify(new ActivityReminder($activity));
                } catch (Exception|Throwable) {
                    // To avoid infinite loops in case there are error, we will mark the activity as notified
                    $activity->markAsReminded();
                } finally {
                    $activity->markAsReminded();
                }
            });
    }
}
