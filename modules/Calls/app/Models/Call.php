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

namespace Modules\Calls\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Calls\Database\Factories\CallFactory;
use Modules\Comments\Concerns\HasComments;
use Modules\Core\Common\Media\HasMedia;
use Modules\Core\Common\Timeline\Timelineable;
use Modules\Core\Concerns\LazyTouchesViaPivot;
use Modules\Core\Contracts\Resources\Resourceable as ResourceableContract;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resourceable;

class Call extends Model implements ResourceableContract
{
    use HasComments,
        HasFactory,
        HasMedia,
        LazyTouchesViaPivot,
        Resourceable,
        Timelineable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'body', 'date', 'call_outcome_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'user_id' => 'int',
        'call_outcome_id' => 'int',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (Call $model) {
            $model->user_id = $model->user_id ?: auth()->id();
        });

        static::deleting(function (Call $model) {
            $model->purge();
        });
    }

    /**
     * A call belongs to outcome.
     */
    public function outcome(): BelongsTo
    {
        return $this->belongsTo(\Modules\Calls\Models\CallOutcome::class, 'call_outcome_id');
    }

    /**
     * Get all of the contacts that are assigned this call.
     */
    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Contact::class, 'callable');
    }

    /**
     * Get all of the companies that are assigned this call.
     */
    public function companies(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Company::class, 'callable');
    }

    /**
     * Get all of the deals that are assigned this call.
     */
    public function deals(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Deals\Models\Deal::class, 'callable');
    }

    /**
     * Get the call owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the timeline component for front-end.
     */
    public function getTimelineComponent(): string
    {
        return 'record-tab-timeline-call';
    }

    /**
     * Get the attributes that may contain pending media.
     */
    public function textAttributesWithMedia(): string
    {
        return 'body';
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->withCount('comments')->with(['user', 'outcome']);
    }

    /**
     * Provide the related pivot relationships to touch.
     */
    protected function relatedPivotRelationsToTouch(): array
    {
        return ['contacts', 'companies', 'deals'];
    }

    /**
     * Purge the call data.
     */
    public function purge(): void
    {
        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $this->{$relation}()->withTrashed()->detach();
        }
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CallFactory
    {
        return CallFactory::new();
    }
}
