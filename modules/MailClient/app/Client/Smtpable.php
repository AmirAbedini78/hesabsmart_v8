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

namespace Modules\MailClient\Client;

use Symfony\Component\Mime\Email;

trait Smtpable
{
    /**
     * The sender email
     *
     * @var string|null
     */
    protected $fromEmail;

    /**
     * The sender name
     *
     * @var string|null
     */
    protected $fromName;

    /**
     * Set the from header email
     *
     * @param  string  $email
     * @return static
     */
    public function setFromAddress($email)
    {
        $this->fromEmail = $email;

        return $this;
    }

    /**
     * Get the from header email
     *
     * @return string|null
     */
    public function getFromAddress()
    {
        return $this->fromEmail;
    }

    /**
     * Set the from header name
     *
     * @param  string  $name
     * @return static
     */
    public function setFromName($name)
    {
        $this->fromName = $name;

        return $this;
    }

    /**
     * Get the from header name
     *
     * @return string|null
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Get the message ID header value.
     */
    public function getMessageId(): string
    {
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === 'message-id') {
                return $value;
            }
        }

        return $this->generateMessageId();
    }

    /**
     * Set the message ID header.
     */
    public function setMessageId(string $id): static
    {
        $this->addHeader('Message-ID', $id);

        return $this;
    }

    /**
     * Generate message ID.
     */
    public function generateMessageId(): string
    {
        return (new Email)->from($this->getFromAddress())->generateMessageId();
    }
}
