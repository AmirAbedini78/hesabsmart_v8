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

namespace Modules\WebForms\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Core\Common\Placeholders\GenericPlaceholder;
use Modules\Core\Common\Placeholders\Placeholders as BasePlaceholders;
use Modules\Core\MailableTemplate\DefaultMailable;
use Modules\MailClient\Mail\MailableTemplate;
use Modules\WebForms\Models\WebForm;
use Modules\WebForms\Services\FormSubmission;

class WebFormSubmitted extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new message instance.
     */
    public function __construct(public WebForm $form, public FormSubmission $submission) {}

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): BasePlaceholders
    {
        return new BasePlaceholders([
            GenericPlaceholder::make('form.title', fn () => $this->form->title),
            GenericPlaceholder::make('payload', fn () => (string) $this->submission)
                ->withStartInterpolation('{{{')
                ->withEndInterpolation('}}}'),
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
        return '<p>There is new submission via the {{ form.title }} web form.<br /><br /></p>
                <p>{{{ payload }}}</p>';
    }

    /**
     * Provides the mail template default subject.
     */
    public static function defaultSubject(): string
    {
        return 'New submission on {{ form.title }} form';
    }
}
