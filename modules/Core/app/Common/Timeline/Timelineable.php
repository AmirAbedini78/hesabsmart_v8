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

namespace Modules\Core\Common\Timeline;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Modules\Core\Models\Model;
use Modules\Core\Models\PinnedTimelineSubject;

/** @mixin \Modules\Core\Models\Model */
trait Timelineable
{
    /**
     * Boot the HasComments trait
     */
    protected static function bootTimelineable(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->pinnedTimelineSubjects()->delete();
            }
        });
    }

    /**
     * Get the timeline pinnable subjects
     */
    public function pinnedTimelineSubjects(): MorphMany
    {
        return $this->morphMany(\Modules\Core\Models\PinnedTimelineSubject::class, 'timelineable');
    }

    /**
     * Get the timeline pin
     */
    public function getPinnedSubject(string $subjectType, int $subjectKey): ?PinnedTimelineSubject
    {
        return $this->pinnedTimelineSubjects->where('subject_id', $subjectKey)
            ->where('subject_type', $subjectType)
            ->first();
    }

    /**
     * Get the timeline identifier
     */
    public static function timelineKey(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the relation name when the model is used as timelineable
     */
    public function getTimelineRelation(): string
    {
        return Str::plural(strtolower(class_basename(get_called_class())));
    }

    /**
     * Get the timeline component for front-end
     */
    public function getTimelineComponent(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the timeline sort column.
     */
    public function getTimelineSortColumn(): string
    {
        return $this->getCreatedAtColumn();
    }

    /**
     * Apply a scope to include the pinned timeline subjects.
     */
    public function scopeWithTimelinePins(Builder $query, $subject): void
    {
        $pinModel = new PinnedTimelineSubject;

        $callback = function ($join) use ($pinModel, $subject) {
            $join->on($this->getQualifiedKeyName(), '=', $pinModel->getTable().'.timelineable_id')
                ->where($pinModel->getTable().'.timelineable_type', static::class)
                ->where($pinModel->getTable().'.subject_id', $subject->getKey())
                ->where($pinModel->getTable().'.subject_type', $subject::class);
        };

        $query->leftJoin($pinModel->getTable(), $callback);
    }
}
