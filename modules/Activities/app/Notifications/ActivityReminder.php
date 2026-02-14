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

namespace Modules\Activities\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Modules\Activities\Mail\ActivityReminder as ReminderMailable;
use Modules\Activities\Models\Activity;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;

class ActivityReminder extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(protected Activity $activity) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): ReminderMailable&MailableTemplate
    {
        return (new ReminderMailable($this->activity))->to($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'path' => $this->activity->resource()->viewRouteFor($this->activity),
            'lang' => [
                'key' => 'activities::activity.notifications.due',
                'attrs' => [
                    'activity' => $this->activity->title,
                    'date' => $this->formattedDate(),
                ],
            ],
        ];
    }

    /**
     * Get the formatted activity date for the user.
     */
    protected function formattedDate(): string
    {
        $dateTime = Carbon::parse(
            $this->activity->due_time ? $this->activity->full_due_date : $this->activity->due_date
        );

        return $dateTime->formatDateTimeForUser($this->activity->user);
    }
}
