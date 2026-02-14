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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Activities\Concerns\HasActivities;
use Modules\Calls\Concerns\HasCalls;
use Modules\Contacts\Concerns\HasPhones;
use Modules\Contacts\Concerns\HasSource;
use Modules\Contacts\Database\Factories\CompanyFactory;
use Modules\Contacts\Observers\CompanyObserver;
use Modules\Core\Common\Media\HasMedia;
use Modules\Core\Common\Timeline\HasTimeline;
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

#[ObservedBy([CompanyObserver::class])]
class Company extends Model implements ResourceableContract
{
    use BroadcastsEvents,
        HasActivities,
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
        'country.name',
        'parent.name',
        'source.name',
        'user.name',
        'industry.name',
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
        'contacts' => 'companies',
        'deals' => 'companies',
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
        'industry_id' => 'int',
        'parent_company_id' => 'int',
        'country_id' => 'int',
        'next_activity_id' => 'int',
        'next_activity_date' => 'datetime',
        'last_contacted_at' => 'datetime',
    ];

    /**
     * Get the parent company
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(\Modules\Contacts\Models\Company::class, 'parent_company_id');
    }

    /**
     * Get all of the company parent companies
     */
    public function parents(): HasMany
    {
        return $this->hasMany(\Modules\Contacts\Models\Company::class, 'parent_company_id');
    }

    /**
     * Get all of the contacts that are associated with the company
     */
    public function contacts(): MorphToMany
    {
        return $this->morphToMany(\Modules\Contacts\Models\Contact::class, 'contactable')
            ->withTimestamps()
            ->orderBy('contactables.created_at');
    }

    /**
     * Get all of the notes for the company
     */
    public function notes(): MorphToMany
    {
        return $this->morphToMany(\Modules\Notes\Models\Note::class, 'noteable');
    }

    /**
     * Get the company owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the company industry
     */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(\Modules\Contacts\Models\Industry::class);
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
     * Purge the company data.
     */
    public function purge(): void
    {
        foreach (['contacts', 'emails', 'deals', 'activities', 'documents'] as $relation) {
            $this->{$relation}()->withTrashedIfUsingSoftDeletes()->detach();
        }

        $this->parents()->update(['parent_company_id' => null]);

        $this->loadMissing('notes')->notes->each(function (Note $note) {
            $note->delete();
        });
    }

    /**
     * Provide the related pivot relationships to touch.
     */
    protected function relatedPivotRelationsToTouch(): array
    {
        return ['contacts', 'deals'];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
