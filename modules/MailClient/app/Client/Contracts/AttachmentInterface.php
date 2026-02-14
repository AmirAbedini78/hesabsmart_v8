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

namespace Modules\MailClient\Client\Contracts;

interface AttachmentInterface
{
    /**
     * Get attachment content id
     *
     * @return string|null
     */
    public function getContentId();

    /**
     * Get the attachment file name
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get the attachment size
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the attachment encoding
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Check whether the attachment is inline
     *
     * @return bool
     */
    public function isInline();

    /**
     * Check whether the attachment is embedded message
     *
     * @return bool
     */
    public function isEmbeddedMessage();
}
