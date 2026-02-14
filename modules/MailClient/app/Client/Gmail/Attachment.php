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

namespace Modules\MailClient\Client\Gmail;

use Modules\MailClient\Client\AbstractAttachment;

class Attachment extends AbstractAttachment
{
    /**
     * Get attachment content id
     *
     * @return string|null
     */
    public function getContentId()
    {
        return $this->getEntity()->getContentId();
    }

    /**
     * Get the attachment file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getEntity()->getFileName();
    }

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getEntity()->getContent();
    }

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getEntity()->getMimeType();
    }

    /**
     * Get the attachment encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->getEntity()->getEncoding();
    }

    /**
     * Check whether the attachment is inline
     *
     * @return bool
     */
    public function isInline()
    {
        return $this->getEntity()->isInline();
    }

    /**
     * Get the attachment size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getEntity()->getSize();
    }

    /**
     * Check whether the attachment is embedded message
     *
     * @return bool
     */
    public function isEmbeddedMessage()
    {
        return $this->getContentType() == 'message/rfc822';
    }
}
