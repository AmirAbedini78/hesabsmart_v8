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

namespace Modules\Core\MailableTemplate;

class DefaultMailable
{
    /**
     * Create new default mail template.
     */
    public function __construct(protected string $htmlMessage, protected string $subject, protected ?string $textMessage = null) {}

    /**
     * Get the mailable default HTML message
     */
    public function htmlMessage(): string
    {
        return $this->htmlMessage;
    }

    /**
     * Get the mailable default text message
     */
    public function textMessage(): ?string
    {
        return $this->textMessage;
    }

    /**
     * Get the mailable default subject
     */
    public function subject(): string
    {
        return $this->subject;
    }
}
