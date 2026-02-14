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
use Modules\Core\Common\Placeholders\ActionButtonPlaceholder;
use Modules\Core\Common\Placeholders\Placeholders;
use Modules\Core\Common\Placeholders\PrivacyPolicyPlaceholder;
use Modules\Core\Common\Placeholders\UrlPlaceholder;
use Modules\Core\MailableTemplate\DefaultMailable;
use Modules\MailClient\Mail\MailableTemplate;
use Modules\Users\Models\User;
use Modules\Users\Placeholders\UserPlaceholder;

class UserMentioned extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
     */
    public function __construct(protected User $mentioned, protected string $mentionUrl, protected User $mentioner) {}

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): Placeholders
    {
        return new Placeholders([
            UserPlaceholder::make(fn () => $this->mentioned->name, 'mentioned_user')
                ->description(__('core::mail_template.placeholders.mentioned_user')),

            UserPlaceholder::make(fn () => $this->mentioner->name)
                ->description(__('core::mail_template.placeholders.user_that_mentions')),

            UrlPlaceholder::make(fn () => $this->mentionUrl, 'url')
                ->description(__('core::mail_template.placeholders.mention_url')),

            ActionButtonPlaceholder::make(fn () => $this->mentionUrl),

            PrivacyPolicyPlaceholder::make(),
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
        return '<p>Hello {{ mentioned_user }}<br /></p>
                <p>{{ user }} mentioned you.<br /></p>
                <p>{{#action_button}}View Record{{/action_button}}</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'You Were Mentioned by {{ user }}';
    }
}
