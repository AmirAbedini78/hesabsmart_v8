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

namespace Modules\Activities\Calendar;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\Event as EventModel;
use Microsoft\Graph\Model\Subscription;
use Modules\Activities\Events\CalendarSyncFinished;
use Modules\Activities\Models\Activity;
use Modules\Activities\Models\Calendar;
use Modules\Core\Common\Synchronization\Exceptions\InvalidSyncNotificationURLException;
use Modules\Core\Contracts\Synchronization\Synchronizable;
use Modules\Core\Contracts\Synchronization\SynchronizesViaWebhook;
use Modules\Core\Facades\MsGraph as Api;
use Modules\Core\Models\Synchronization;

class OutlookCalendarSync extends CalendarSynchronization implements Synchronizable, SynchronizesViaWebhook
{
    protected string $webHookUrl;

    /**
     * Initialize new OutlookCalendarSync class
     */
    public function __construct(protected Calendar $calendar)
    {
        $this->webHookUrl = URL::to(config('app.url').'/webhook/outlook-calendar');
    }

    /**
     * Synchronize the data for the given synchronization
     */
    public function synchronize(Synchronization $synchronization, $failWhenThrottled = false): void
    {
        Api::connectUsing($this->calendar->email);

        try {
            /** @var \Microsoft\Graph\Http\GraphCollectionRequest */
            $iterator = Api::immutable(
                fn () => Api::createCollectionGetRequest($this->createEndpoint())
                    ->setReturnType(EventModel::class)
                    ->setPageSize(100)
            );

            $changesPerformed = false;

            while (! $iterator->isEnd()) {
                if ($this->processChangedEvents($iterator->getPage() ?: [])) {
                    $changesPerformed = true;
                }
            }

            $synchronization->updateLastSyncDate();

            CalendarSyncFinished::dispatchIf($changesPerformed, $synchronization->synchronizable);
        } catch (IdentityProviderException) {
            $this->calendar->oAuthAccount->setAuthRequired();
        } catch (ClientException $e) {
            /**
             * {"error":{"code":"ApplicationThrottled","message":"Application is over its MailboxConcurrency limit."}}
             *
             * @see https://stackoverflow.com/questions/73372287/microsoft-graph-batch-api-concurrent-request-reduction-to-4-where-as-outlook-had
             * @see https://learn.microsoft.com/en-us/answers/questions/734143/mailboxconcurrency-limit-throttling-exception
             */
            $errorDetails = json_decode($e->getResponse()->getBody()->getContents(), true);

            $errorCode = $errorDetails['error']['code'] ?? 'Unknown error code';

            if ($errorCode === 'ApplicationThrottled' && ! $failWhenThrottled) {
                sleep(1);

                $this->synchronize($synchronization, true);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Iterage over the changed events
     *
     * @param  \Microsoft\Graph\Model\Event[]  $events
     */
    protected function processChangedEvents(array $events): bool
    {
        foreach ($events as $event) {
            [$model, $guestsUpdated] = $this->processViaChange(
                $this->attributesFromEvent($event),
                $this->determineUser($event->getOrganizer()?->getEmailAddress()?->getAddress(), $this->calendar->user),
                $event->getId(),
                $event->getICalUId(),
                $this->calendar
            );

            if ($model->wasRecentlyCreated || $model->wasChanged() || $guestsUpdated) {
                $changesPerformed = true;
            }
        }

        return $changesPerformed ?? false;
    }

    /**
     * Create attributes from event
     */
    protected function attributesFromEvent(Event $event): array
    {
        $dueDate = Carbon::parse($event->getStart()->getDateTime());
        $endDate = Carbon::parse($event->getEnd()->getDateTime());
        $isAllDay = $event->getIsAllDay();

        return [
            'title' => $event->getSubject() ?? '(No Title)',
            'description' => $event->getBody()->getContent(),
            'due_date' => $dueDate->format('Y-m-d'),
            'due_time' => ! $isAllDay ? $dueDate->format('H:i').':00' : null,
            'end_date' => ($isAllDay ? $endDate->sub(1, 'second') : $endDate)->format('Y-m-d'),
            'end_time' => ! $isAllDay ? $endDate->format('H:i').':00' : null,
            'reminder_minutes_before' => $event->getReminderMinutesBeforeStart(),
            'guests' => collect($event->getAttendees())->map(function (array $attendee) {
                return [
                    'email' => $attendee['emailAddress']['address'] ?? null,
                    'name' => $attendee['emailAddress']['name'],
                ];
            })->all(),
        ];
    }

    /**
     * Subscribe for changes for the given synchronization
     */
    public function watch(Synchronization $synchronization): void
    {
        $this->handleRequestExceptions(function () use ($synchronization) {
            try {
                $subscription = $this->createSubscriptionInstance($synchronization)->getProperties();

                $subscription = Api::immutable(
                    fn () => Api::createPostRequest('/subscriptions', $subscription)->setReturnType(Subscription::class)->execute()
                );

                $synchronization->markAsWebhookSynchronizable(
                    $subscription->getId(),
                    $subscription->getExpirationDateTime(),
                );
            } catch (ClientException $e) {
                // We will throw an exceptions for invalid URL and won't allow the
                // user to sync without valid URL as the Outlook synchronization works only
                // with webhooks and cannot use the polling method as we cannot detect deleted events when polling
                if ($this->isInvalidExceptionUrlMessage($e->getMessage())) {
                    throw new InvalidSyncNotificationURLException;
                }

                throw $e;
            }
        });
    }

    /**
     * Unsubscribe from changes for the given synchronization
     */
    public function unwatch(Synchronization $synchronization): void
    {
        // perhaps subscription for some reason not created? e.q. for notificationUrl validation failed
        if ($resourceId = $synchronization->resource_id) {
            $this->handleRequestExceptions(function () use ($synchronization, $resourceId) {
                Api::immutable(
                    fn () => Api::createDeleteRequest('/subscriptions/'.$resourceId)->execute()
                );

                $synchronization->unmarkAsWebhookSynchronizable();
            });
        }
    }

    /**
     * Update event in the calendar from the given activity
     */
    public function updateEvent(Activity $activity, string $eventId): void
    {
        $this->handleRequestExceptions(function () use ($activity, $eventId) {
            $endpoint = $this->endpoint('/'.$eventId);
            $payload = OutlookEventPayload::make($activity);

            Api::immutable(
                fn () => Api::createPatchRequest($endpoint, $payload)->execute()
            );
        });
    }

    /**
     * Create event in the calendar from the given activity
     */
    public function createEvent(Activity $activity): void
    {
        $this->handleRequestExceptions(function () use ($activity) {
            $endpoint = $this->endpoint();
            $payload = new OutlookEventPayload($activity);

            $event = Api::immutable(
                fn () => Api::createPostRequest($endpoint, $payload)->setReturnType(EventModel::class)->execute()
            );

            $activity->addSynchronization($event->getId(), $this->calendar->getKey(), [
                'icaluid' => $event->getICalUId(),
            ]);
        });
    }

    /**
     * Update event from the calendar for the given activity
     */
    public function deleteEvent(int $activityId, string $eventId): void
    {
        $this->handleRequestExceptions(function () use ($activityId, $eventId) {
            $endpoint = $this->endpoint('/'.$eventId);

            try {
                Api::immutable(fn () => Api::createDeleteRequest($endpoint)->execute());
            } catch (RequestException $e) {
                // https://stackoverflow.com/questions/55875130/calls-to-events-returning-error-this-operation-does-not-support-binding-to-a
                if (! str_contains($e->getMessage(), 'This operation does not support binding to a non-calendar folder')) {
                    throw $e;
                }
            }

            Activity::find($activityId)?->deleteSynchronization($eventId, $this->calendar->getKey());
        });
    }

    /**
     * Prepare the endpoint to retrieve the events
     */
    protected function createEndpoint(): string
    {
        $startFrom = new \DateTime($this->calendar->startSyncFrom());

        $endpoint = $this->endpoint();

        $endpoint .= '?$filter=createdDateTime ge '.$startFrom->format('Y-m-d\TH:i:s\Z');
        $endpoint .= ' and type eq \'singleInstance\'';
        $endpoint .= ' and isDraft eq false';
        // There are times when I have a personal appointment during the work day that needs to be on my work calendar but not synced to Concord.
        // Having the ability to exclude calendar items marked as Private would solve this problem.
        $endpoint .= ' and sensitivity ne \'private\'';

        return $endpoint;
    }

    /**
     * Helper function to handle the requests common exception
     */
    protected function handleRequestExceptions(\Closure $callable): void
    {
        Api::connectUsing($this->calendar->email);

        try {
            $callable();
        } catch (ClientException $e) {
            throw_if($e->getCode() !== 404, $e);
        } catch (IdentityProviderException $e) {
            $this->calendar->oAuthAccount->setAuthRequired();
        }
    }

    /**
     * Create new Microsoft Subscription instance
     */
    protected function createSubscriptionInstance(Synchronization $synchronization): Subscription
    {
        return (new Subscription)->setChangeType('created,updated,deleted')
            ->setNotificationUrl($this->webHookUrl)
            ->setClientState($synchronization->id) // uuid;
            // https://docs.microsoft.com/en-us/graph/api/resources/subscription?view=graph-rest-1.0#maximum-length-of-subscription-per-resource-type
            ->setExpirationDateTime(now()->addDays(2))
            ->setResource($this->endpoint());
    }

    /**
     * Check whether the given exception message is invalid url
     */
    protected function isInvalidExceptionUrlMessage(string $message): bool
    {
        return Str::of($message)->lower()->contains([
            'invalid notification url',
            'subscription validation request failed',
            '\'http\' is not supported',
            'the remote name could not be resolved',
        ]);
    }

    /**
     * Create endpoint for the calendar
     */
    protected function endpoint(string $glue = ''): string
    {
        return '/me/calendars/'.$this->calendar->calendar_id.'/events'.$glue;
    }
}
