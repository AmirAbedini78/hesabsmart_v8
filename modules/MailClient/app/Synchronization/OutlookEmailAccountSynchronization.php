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

namespace Modules\MailClient\Synchronization;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Microsoft\Graph\Model\Message as GraphMessageModel;
use Modules\MailClient\Client\Exceptions\ConnectionErrorException;
use Modules\MailClient\Client\Exceptions\RateLimitExceededException;
use Modules\MailClient\Models\EmailAccountFolder;

class OutlookEmailAccountSynchronization extends EmailAccountIdBasedSynchronization
{
    /**
     * The delta meta key.
     */
    const DELTA_META_KEY = 'deltaLink';

    /**
     * Mode for the sync process.
     */
    protected string $mode = self::FORCE_MODE;

    /**
     * Limit the Graph messages request.
     */
    protected int $limit = 500;

    /**
     * Start account messages synchronization.
     */
    public function syncMessages(): void
    {
        $this->account->activeFolders()->map(
            fn (EmailAccountFolder $folder) => [
                'folder' => $folder,
                'messages' => $this->getMessagesToSyncAndQueueForDeletition(
                    $folder, $folder->getMeta(static::DELTA_META_KEY)
                ),
            ]
        )->each(function (array $data) {
            $this->info(sprintf('Performing sync for folder %s via delta link.', $data['folder']->name));

            $this->processMessages($data['messages']);
        });
    }

    /**
     * Get all messages and queue deleted messages
     * so we can use the messages from the queue to handle moved messages.
     */
    protected function getMessagesToSyncAndQueueForDeletition(EmailAccountFolder $folder, ?string $currentDeltaLink): Collection
    {
        /** @var \Modules\MailClient\Client\Outlook\Folder */
        $remoteFolder = $this->findFolder($folder);

        $messages = collect([]);

        $isAccountInitialSync = $this->account->isInitialSyncPerformed();
        $initialSyncFrom = $this->account->initial_sync_from->format('Y-m-d H:i:s');

        $newDeltaLink = null;

        /**
         * Should not be needed if we update go Graph API SDK v2?
         *
         * @todo https://github.com/microsoftgraph/msgraph-sdk-php/issues/68
         */
        try {
            $graphCollection = $remoteFolder->createDeltaOrGetMessagesRequest(
                $currentDeltaLink, $initialSyncFrom
            )->setPageSize($this->limit);

            while (! $graphCollection->isEnd()) {
                $messages->push(...$graphCollection->getPage() ?? []);
            }

            $newDeltaLink = $graphCollection->getDeltaLink();
        } catch (IdentityProviderException $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if ($response->getStatusCode() === 429) {
                $retryAfter = now()->addSeconds(
                    (int) $response->getHeader('Retry-After')[0] ?? 300
                )->format('Y-m-d H:i:s');

                throw new RateLimitExceededException('Rate limit exceeded.', $retryAfter, $e);
            } elseif ($response->getStatusCode() === 410) {
                // {"error":{"code":"SyncStateNotFound","message":"The sync state identified using the request token 'TOKEN' is not found [entryId=00000]."}}
                $errorDetails = json_decode($response->getBody()->getContents(), true);
                $errorCode = $errorDetails['error']['code'] ?? 'Unknown error code';

                if ($errorCode === 'SyncStateNotFound') {
                    // Perform full sync
                    $messages = $this->getMessagesToSyncAndQueueForDeletition($folder, null);
                }
            } else {
                throw $e;
            }
        }

        /**
         * Check if it's trash or spam folder and there is no initial sync for the account
         * If yes, only save the new delta link because trash or spam are not synced on the inital sync.
         */
        if (! $isAccountInitialSync && $remoteFolder->isTrashOrSpam()) {
            $folder->setMeta(static::DELTA_META_KEY, $newDeltaLink);

            return collect([]);
        }

        /**
         * Make the messages unique based on their ID as Microsoft
         * does not guarantee that the messages in delta will be unique
         * then we will batch get every message.
         */
        $messages = $messages->unique(fn (GraphMessageModel $message) => $message->getId())->values();

        /**
         * Queue messages for removal first and remove it from the messages collection as we don't need them.
         */
        if (! $this->isFolderInitialSync($folder)) {
            $messages = $this->checkForRemovedMessages($messages, $folder);
        }

        /**
         * We need to get all changes messages via batch so we can perform full
         * update to the message in case the message exists in database
         * The function also will retrieve openExtensions and the headers e.q. references
         * and in-reply to because it's not possible to retieve the headers via delta as
         * Microsoft does not return them for sent messages.
         */
        $messages = $messages->replace($this->getImapClient()->batchGetMessages($messages));

        /**
         * And after processing update the folder delta link
         * In case of failures to catch the messages again
         * Because we are checking if the message exists in database
         * In this case, no duplicate messages will be created.
         */
        $folder->setMeta(static::DELTA_META_KEY, $newDeltaLink);

        return $messages;
    }

    /**
     * Handle any removed messages via delta.
     *
     * Removed messages can exists only when fetching the data via deltaLink.
     */
    protected function checkForRemovedMessages(Collection $messages, EmailAccountFolder $folder): Collection
    {
        return $messages->filter(function (GraphMessageModel $message) use ($folder) {
            if (isset($message->getProperties()['@removed'])) {
                $this->addMessageToDeleteQueue($message->getId(), $folder);

                return false;
            }

            return true;
        })->values();
    }

    /**
     * Check whether the sync is initial one, the check is performed based on the delta link.
     */
    protected function isFolderInitialSync(EmailAccountFolder $folder): bool
    {
        return is_null($folder->getMeta(static::DELTA_META_KEY));
    }
}
