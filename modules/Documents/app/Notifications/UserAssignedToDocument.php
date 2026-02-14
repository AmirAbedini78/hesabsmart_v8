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

namespace Modules\Documents\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;
use Modules\Documents\Mail\UserAssignedToDocument as UserAssignedToDocumentMailable;
use Modules\Documents\Models\Document;
use Modules\Users\Models\User;

class UserAssignedToDocument extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     */
    public function __construct(protected Document $document, protected User $assigneer) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): UserAssignedToDocumentMailable&MailableTemplate
    {
        return (new UserAssignedToDocumentMailable($this->document, $this->assigneer))->to($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'path' => $this->document->resource()->viewRouteFor($this->document),
            'lang' => [
                'key' => 'documents::document.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'title' => $this->document->title,
                ],
            ],
        ];
    }
}
