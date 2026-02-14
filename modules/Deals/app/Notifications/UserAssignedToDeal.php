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

namespace Modules\Deals\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;
use Modules\Deals\Mail\UserAssignedToDeal as AssignedToDealMailable;
use Modules\Deals\Models\Deal;
use Modules\Users\Models\User;

class UserAssignedToDeal extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(protected Deal $deal, protected User $assigneer) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): AssignedToDealMailable&MailableTemplate
    {
        return (new AssignedToDealMailable($this->deal, $this->assigneer))->to($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'path' => $this->deal->resource()->viewRouteFor($this->deal),
            'lang' => [
                'key' => 'deals::deal.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->deal->name,
                ],
            ],
        ];
    }
}
