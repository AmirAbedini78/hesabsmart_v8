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

use Modules\Core\Contracts\HasNotificationsSettings;
use Modules\Core\MailableTemplate\MailableTemplate;
use Modules\Core\Notification;
use Modules\Users\Mail\ResetPassword as ResetPasswordMailable;

class ResetPassword extends Notification
{
    /**
     * Indicates whether the notification is user configurable.
     */
    public static bool $configurable = false;

    /**
     * Create a notification instance.
     */
    public function __construct(public string $token) {}

    /**
     * Get the notification's channels.
     */
    public function via(HasNotificationsSettings $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): ResetPasswordMailable&MailableTemplate
    {
        return (new ResetPasswordMailable($this->resetUrl($notifiable)))->to($notifiable);
    }

    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl(object $notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
