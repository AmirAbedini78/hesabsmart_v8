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

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\ModelFinder;

class Timelineables
{
    /**
     * Discover and register the timelineables.
     */
    public static function discover(): void
    {
        $instance = new static;

        $timelineables = $instance->getTimelineables()->all();

        foreach ($instance->getSubjects() as $subject) {
            static::register($timelineables, $subject);
        }
    }

    /**
     * Register the given timelineables.
     */
    public static function register(string|array $timelineables, string $subject): void
    {
        Timeline::acceptsPinsFrom([
            'subject' => $subject,
            'as' => $subject::getTimelineSubjectKey(),
            'accepts' => array_map(function ($class) {
                return ['as' => $class::timelineKey(), 'timelineable_type' => $class];
            }, Arr::wrap($timelineables)),
        ]);
    }

    /**
     * Get the timelineables.
     */
    public function getTimelineables(): Collection
    {
        return Cache::rememberForever('timelineables', function () {
            return collect((new ModelFinder)->find())
                ->filter(fn (string $model) => static::isTimelineable($model))
                ->values();
        });
    }

    /**
     * Check whether the given model is timelineable.
     *
     * @param  \Modules\Core\Models\Model|string  $model
     */
    public static function isTimelineable($model): bool
    {
        return in_array(Timelineable::class, class_uses_recursive($model));
    }

    /**
     * Check whether the given model has timeline.
     *
     * @param  \Modules\Core\Models\Model|string  $model
     */
    public static function hasTimeline($model): bool
    {
        return in_array(HasTimeline::class, class_uses_recursive($model));
    }

    /**
     * Get the subjects.
     */
    public function getSubjects(): Collection
    {
        return Cache::rememberForever('timeline-subjects', function () {
            return collect((new ModelFinder)->find())
                ->filter(fn (string $model) => static::hasTimeline($model))
                ->values();
        });
    }
}
