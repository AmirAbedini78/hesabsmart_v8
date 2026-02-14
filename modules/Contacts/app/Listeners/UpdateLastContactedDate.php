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

namespace Modules\Contacts\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Modules\Calls\Models\Call;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\MailClient\Client\Events\MessageSent;

class UpdateLastContactedDate implements ShouldHandleEventsAfterCommit
{
    /**
     * When message is sent via email client, update last contacted date for the models.
     */
    public function handle(MessageSent|Call $event): void
    {
        if ($event instanceof Call) {
            foreach (['contacts', 'companies'] as $relation) {
                $relationQuery = $event->{$relation}();
                $relatedModel = $relationQuery->getModel();

                $ids = $relationQuery->get(['id'])->modelKeys();

                if (count($ids)) {
                    $this->updateLastContactedDate(
                        $relatedModel->newQuery()->whereIn($relatedModel->getKeyName(), $ids)
                    );
                }
            }
        } elseif ($event instanceof MessageSent) {
            if ($emails = $this->extractEmails($event)) {
                foreach ([Contact::class, Company::class] as $modelName) {
                    $this->updateLastContactedDate($modelName::withTrashed()->whereIn('email', $emails));
                }
            }
        }
    }

    /**
     * Update the last contacted date for the given model query.
     */
    protected function updateLastContactedDate(Builder $query): void
    {
        $query->getModel()->withoutTouching(function () use ($query) {
            $query->where(function (Builder $query) {
                $query->whereNull('last_contacted_at')->orWhere('last_contacted_at', '<', now());
            })->update(['last_contacted_at' => now()]);
        });
    }

    /**
     * Extract the emails from the client.
     */
    protected function extractEmails(MessageSent $event): array
    {
        return collect($event->client->getTo())
            ->merge($event->client->getCc())
            ->pluck('address')
            ->filter()
            ->unique()
            ->all();
    }
}
