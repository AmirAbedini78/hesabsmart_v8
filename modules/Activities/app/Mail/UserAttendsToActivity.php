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

namespace Modules\Activities\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Activities\Contracts\Attendeeable;
use Modules\Activities\Models\Activity;
use Modules\Activities\Resources\Activity as ResourceActivity;
use Modules\Core\Common\Placeholders\ActionButtonPlaceholder;
use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Common\Placeholders\PrivacyPolicyPlaceholder;
use Modules\Core\MailableTemplate\DefaultMailable;
use Modules\Core\Resource\ResourcePlaceholders;
use Modules\MailClient\Mail\MailableTemplate;

class UserAttendsToActivity extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
     */
    public function __construct(protected Attendeeable $guestable, protected Activity $activity) {}

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): ResourcePlaceholders
    {
        return ResourcePlaceholders::make(new ResourceActivity, $this->activity ?? null)
            ->push([
                ActionButtonPlaceholder::make(fn () => $this->activity),
                PrivacyPolicyPlaceholder::make(),
                GenericPlaceholder::make('guest_name', fn () => $this->guestable->getGuestDisplayName())
                    ->description(__('activities::activity.guest')),
                GenericPlaceholder::make('guest_email', fn () => $this->guestable->getGuestEmail()),
            ])
            ->withUrlPlaceholder();
    }

    /**
     * Build the mailable template with additional data
     *
     * @return static
     */
    public function build()
    {
        $data = $this->activity->generateICSInstance()->get();

        return $this->attachData($data, 'invite.ics', [
            'mime' => 'text/calendar',
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
        return '<p>Hello {{ guest_name }}<br /></p>
                <p>You have been added as a guest of the {{ activity.title }} activity<br /></p>
                <p>{{#action_button}}View Activity{{/action_button}}</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'You have been added as guest to activity';
    }
}
