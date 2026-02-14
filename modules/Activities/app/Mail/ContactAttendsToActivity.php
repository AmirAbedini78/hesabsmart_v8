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

use Modules\Core\Models\MailableTemplate;
use Modules\Core\Resource\ResourcePlaceholders;

class ContactAttendsToActivity extends UserAttendsToActivity
{
    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): ResourcePlaceholders
    {
        return parent::placeholders()->forget([
            'activity.url', 'action_button', 'activity.note',
            'activity.updated_at', 'activity.reminder_minutes_before',
            'activity.owner_assigned_date', 'activity.reminded_at',
        ]);
    }

    /**
     * Get the mailable template model
     *
     * @return \Modules\Core\Models\MailableTemplate
     */
    public function getMailableTemplate()
    {
        // The contacts does not have an option to specify which locale to use
        // in this case, we will use the actual activity owner locale
        // if in future is added field for contact locale, use the contact locale instead.
        $locale = $this->locale ?? $this->activity->user->preferredLocale();

        return $this->templateModel ??= MailableTemplate::forLocale($locale, static::class)->first();
    }

    /**
     * Provides the mail template default message.
     */
    public static function defaultHtmlTemplate(): string
    {
        return '<p>Hello {{ guest_name }}<br /></p>
                <p>You have been added as a guest of the {{ activity.title }} activity.</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'You have been added as guest to activity';
    }
}
