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

namespace Modules\Contacts\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Resources\Company as ResourceCompany;
use Modules\Core\Common\Placeholders\ActionButtonPlaceholder;
use Modules\Core\Common\Placeholders\PrivacyPolicyPlaceholder;
use Modules\Core\MailableTemplate\DefaultMailable;
use Modules\Core\Resource\ResourcePlaceholders;
use Modules\MailClient\Mail\MailableTemplate;
use Modules\Users\Models\User;
use Modules\Users\Placeholders\UserPlaceholder;

class UserAssignedToCompany extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
     */
    public function __construct(protected Company $company, protected User $assigneer) {}

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): ResourcePlaceholders
    {
        return ResourcePlaceholders::make(new ResourceCompany, $this->company ?? null)
            ->push([
                ActionButtonPlaceholder::make(fn () => $this->company),
                PrivacyPolicyPlaceholder::make(),
                UserPlaceholder::make(fn () => $this->assigneer->name, 'assigneer')
                    ->description(__('contacts::company.mail_placeholders.assigneer')),
            ])
            ->withUrlPlaceholder();
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
        return '<p>Hello {{ company.user }}<br /></p>
                <p>You have been assigned to a company {{ company.name }} by {{ assigneer }}<br /></p>
                <p>{{#action_button}}View Company{{/action_button}}</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'You are added as an owner of the company {{ company.name }}';
    }
}
