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

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PinnedTimelineSubject extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('default_order', function (Builder $query) {
            $query->latest();
        });
    }

    /**
     * Pin activity to the given subject
     *
     * @param  int  $subjectId
     * @param  string  $subjectType
     * @param  int  $timelineabeId
     * @param  string  $timelineableType
     * @return \Modules\Core\Models\PinnedTimelineSubject
     */
    public function pin($subjectId, $subjectType, $timelineabeId, $timelineableType)
    {
        $this->fill([
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'timelineable_id' => $timelineabeId,
            'timelineable_type' => $timelineableType,
        ])->save();

        return $this;
    }

    /**
     * Unpin activity from the given subject
     *
     * @param  int  $subjectId
     * @param  string  $subjectType
     * @param  int  $timelineableId
     * @param  string  $timelineableType
     * @return bool
     */
    public function unpin($subjectId, $subjectType, $timelineableId, $timelineableType)
    {
        $this->where([
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'timelineable_id' => $timelineableId,
            'timelineable_type' => $timelineableType,
        ])->delete();
    }

    /**
     * Get the subject of the pinned timeline
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the timelineable
     */
    public function timelineable(): MorphTo
    {
        return $this->morphTo();
    }
}
