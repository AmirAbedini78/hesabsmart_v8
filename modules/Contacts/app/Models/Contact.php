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

namespace Modules\Contacts\Models;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Activities\Concerns\HasActivities;
use Modules\Activities\Contracts\Attendeeable;
use Modules\Calls\Concerns\HasCalls;
use Modules\Contacts\Concerns\HasPhones;
use Modules\Contacts\Concerns\HasSource;
use Modules\Contacts\Database\Factories\ContactFactory;
use Modules\Contacts\Observers\ContactObserver;
use Modules\Core\Common\Media\HasMedia;
use Modules\Core\Common\Timeline\HasTimeline;
use Modules\Core\Concerns\HasAvatar;
use Modules\Core\Concerns\HasCountry;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Concerns\HasTags;
use Modules\Core\Concerns\HasUuid;
use Modules\Core\Concerns\LazyTouchesViaPivot;
use Modules\Core\Concerns\Prunable;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resourceable;
use Modules\Core\Workflow\HasWorkflowTriggers;
use Modules\Deals\Concerns\HasDeals;
use Modules\Documents\Concerns\HasDocuments;
use Modules\MailClient\Concerns\HasEmails;
use Modules\Notes\Models\Note;

#[ObservedBy([ContactObserver::class])]
class Contact extends Model implements Attendeeable, ResourceableContract
{
    use BroadcastsEvents,
        HasActivities,
        HasAvatar,
        HasCalls,
        HasCountry,
        HasCreator,
        HasDeals,
        HasDocuments,
        HasEmails,
        HasFactory,
        HasMedia,
        HasPhones,
        HasSource,
        HasTags,
        HasTimeline,
        HasUuid,
        HasWorkflowTriggers,
        LazyTouchesViaPivot,
        Prunable,
        Resourceable,
        SoftDeletes;

    const TAGS_TYPE = 'contacts';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [
        'created_by',
        'created_at',
        'updated_at',
        'owner_assigned_date',
        'next_activity_id',
        'uuid',
    ];

    /**
     * Attributes and relations to log changelog for the model
     *
     * @var array
     */
    protected static $changelogAttributes = [
        '*',
        'user.name',
        'country.name',
        'source.name',
    ];

    /**
     * Exclude attributes from the changelog
     *
     * @var array
     */
    protected static $changelogAttributeToIgnore = [
        'updated_at',
        'created_at',
        'created_by',
        'owner_assigned_date',
        'next_activity_id',
        'deleted_at',
    ];

    /**
     * Provides the relationships for the pivot logger
     *
     * [ 'main' => 'reverse']
     */
    protected static array $logPivotEventsOn = [
        'companies' => 'contacts',
        'deals' => 'contacts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'owner_assigned_date' => 'datetime',
        'user_id' => 'int',
        'created_by' => 'int',
        'source_id' => 'int',
        'country_id' => 'int',
        'next_activity_id' => 'int',
        'next_activity_date' => 'datetime',
        'last_contacted_at' => 'datetime',
    ];

    /**
     * Get all of the companies that are associated with the contact
     */
    public function companies(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Company::class, 'contactable')
            ->withTimestamps()
            ->orderBy('contactables.created_at');
    }

    /**
     * Get all of the notes for the contact
     */
    public function notes(): MorphToMany
    {
        return $this->morphToMany(\Modules\Notes\Models\Note::class, 'noteable');
    }

    /**
     * Get all of the contact guests models
     */
    public function guests(): MorphMany
    {
        return $this->morphMany(\Modules\Activities\Models\Guest::class, 'guestable');
    }

    /**
     * Get the contact owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the person email address when guest
     */
    public function getGuestEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get the person displayable name when guest
     */
    public function getGuestDisplayName(): string
    {
        return $this->full_name;
    }

    /**
     * Get the notification that should be sent to the person when is added as guest
     *
     * @return string
     */
    public function getAttendeeNotificationClass()
    {
        return \Modules\Activities\Mail\ContactAttendsToActivity::class;
    }

    /**
     * Indicates whether the notification should be send to the guest
     */
    public function shouldSendAttendingNotification(Attendeeable $model): bool
    {
        return (bool) settings('send_contact_attends_to_activity_mail');
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn($event)
    {
        // Currently only the updated event is used
        return match ($event) {
            'updated' => [new PrivateChannel($this)],
            default => null,
        };
    }

    /**
     * Create a new broadcastable model event for the model.
     *
     * @return \Illuminate\Database\Eloquent\BroadcastableModelEventOccurred
     */
    protected function newBroadcastableEvent(string $event)
    {
        return (new BroadcastableModelEventOccurred(
            $this,
            $event
        ))->dontBroadcastToCurrentUser();
    }

    /**
     * Purge the contact data.
     */
    public function purge(): void
    {
        foreach (['companies', 'emails', 'deals', 'activities', 'documents'] as $relation) {
            $this->{$relation}()->withTrashedIfUsingSoftDeletes()->detach();
        }

        $this->guests()->forceDelete();

        $this->loadMissing('notes')->notes->each(function (Note $note) {
            $note->delete();
        });
    }

    /**
     * Get the contact full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (null $value, array $attributes) {
                $lastName = $attributes['last_name'] ?? '';

                return trim("{$attributes['first_name']} $lastName");
            },
        );
    }

    /**
     * Raw concat attributes for query
     *
     * @param  array  $attributes
     * @param  string  $separator
     * @return \Illuminate\Database\Query\Expression|null
     */
    public static function nameQueryExpression($as = null)
    {
        $driver = (new static)->getConnection()->getDriverName();

        return match ($driver) {
            'mysql', 'pgsql', 'mariadb' => DB::raw('RTRIM(CONCAT(first_name, \' \', COALESCE(last_name, \'\')))'.($as ? ' as '.$as : '')),
            'sqlite' => DB::raw('RTRIM(first_name || \' \' || last_name)'.($as ? ' as '.$as : '')),
            default => throw new \Exception('Unsupported driver: '.$driver),
        };
    }

    /**
     * Provide the related pivot relationships to touch.
     */
    protected function relatedPivotRelationsToTouch(): array
    {
        return ['companies', 'deals'];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }
}
