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
use Modules\Activities\Contracts\Attendeeable;
use Modules\Activities\Mail\UserAttendsToActivity as UserAttendsToActivityMailable;
use Modules\Activities\Models\Activity;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;

class UserAttendsToActivity extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(protected Attendeeable $guestable, protected Activity $activity) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): UserAttendsToActivityMailable&MailableTemplate
    {
        return (new UserAttendsToActivityMailable($this->guestable, $this->activity))->to($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'path' => $this->activity->resource()->viewRouteFor($this->activity),
            'lang' => [
                'key' => 'activities::activity.notifications.added_as_guest',
                'attrs' => [],
            ],
        ];
    }
}
