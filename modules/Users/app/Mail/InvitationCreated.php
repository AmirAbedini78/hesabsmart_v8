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

namespace Modules\Users\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Common\Placeholders\Placeholders;
use Modules\Core\MailableTemplate\DefaultMailable;
use Modules\MailClient\Mail\MailableTemplate;
use Modules\Users\Models\UserInvitation;

class InvitationCreated extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new message instance.
     */
    public function __construct(public UserInvitation $invitation) {}

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): Placeholders
    {
        return new Placeholders([
            GenericPlaceholder::make('email', fn () => $this->invitation->email),
            GenericPlaceholder::make('invitation_url', fn () => $this->invitation->link),
            GenericPlaceholder::make('link_expires_after', config('users.invitation.expires_after')),
        ]);
    }

    /**
     * Provides the mail template default configuration.
     */
    public static function default(): DefaultMailable
    {
        return new DefaultMailable(static::defaultHtmlTemplate(), static::defaultSubject());
    }

    /**
     * Provides the mail template default message.
     */
    public static function defaultHtmlTemplate(): string
    {
        return '<p>Hi {{ email }}<br /></p>
                <p>Someone has invited you to access their CRM software.</p>
                <p><a href="{{ invitation_url }}">Click here</a> to activate your account!</p>
                <p>Please note that the link expires after {{ link_expires_after }} days, so make sure to create the account in {{ link_expires_after }} days.</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'You have been invited to join';
    }
}
