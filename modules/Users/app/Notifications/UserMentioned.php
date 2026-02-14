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

namespace Modules\Users\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;
use Modules\Users\Mail\UserMentioned as UserMentionedMailable;
use Modules\Users\Models\User;

class UserMentioned extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public string $mentionUrl, public User $mentioner) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): UserMentionedMailable&MailableTemplate
    {
        return (new UserMentionedMailable(
            $notifiable,
            $this->mentionUrl,
            $this->mentioner
        ))->to($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'path' => $this->mentionUrl,
            'lang' => [
                'key' => 'users::user.notifications.user_mentioned',
                'attrs' => [
                    'name' => $this->mentioner->name,
                ],
            ],
        ];
    }
}
