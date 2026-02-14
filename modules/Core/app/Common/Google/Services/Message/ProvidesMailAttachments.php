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

namespace Modules\Core\Common\Google\Services\Message;

use Illuminate\Support\Collection;

trait ProvidesMailAttachments
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $attachments;

    /**
     * Check whether the message has attachments
     *
     * @return bool
     */
    public function hasAttachments()
    {
        return ! $this->getAttachments()->isEmpty();
    }

    /**
     * Number of attachments of the message.
     *
     * @return int
     */
    public function countAttachments()
    {
        return $this->getAttachments()->count();
    }

    /**
     * Get the message attachments
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttachments()
    {
        if (! is_null($this->attachments)) {
            return $this->attachments;
        }

        $attachments = new Collection;
        $parts = $this->getAllParts($this->parts);

        foreach ($parts as $part) {
            if (! empty($part->body->attachmentId)) {
                $attachments->push(new Attachment($this->client, $this->getId(), $part));
            }
        }

        return $this->attachments = $attachments;
    }
}
