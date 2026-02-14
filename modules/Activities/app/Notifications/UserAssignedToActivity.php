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
use Modules\Activities\Mail\UserAssignedToActivity as UserAssignedToActivityMailable;
use Modules\Activities\Models\Activity;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;
use Modules\Users\Models\User;

class UserAssignedToActivity extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(protected Activity $activity, protected User $assigneer) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): UserAssignedToActivityMailable&MailableTemplate
    {
        return (new UserAssignedToActivityMailable($this->activity, $this->assigneer))->to($notifiable);
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
                'key' => 'activities::activity.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->activity->title,
                ],
            ],
        ];
    }
}
