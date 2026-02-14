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

namespace Modules\MailClient\Client\Compose;

use Modules\MailClient\Client\Client;
use Modules\MailClient\Client\FolderIdentifier;

class MessageReply extends AbstractComposer
{
    /**
     * Create new MessageReply instance.
     */
    public function __construct(
        Client $client,
        protected string|int $remoteId,
        protected FolderIdentifier $folder,
        ?FolderIdentifier $sentFolder = null
    ) {
        parent::__construct($client, $sentFolder);
    }

    /**
     * Reply to the message.
     *
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->reply($this->remoteId, $this->folder);
    }
}
