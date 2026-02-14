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

class Timeline
{
    /**
     * Registered pinable subjects.
     */
    protected static array $pinableSubjects = [];

    /**
     * Register pinable subject.
     */
    public static function acceptsPinsFrom(array $subject): void
    {
        if (isset(static::$pinableSubjects[$subject['as']])) {
            // If exists, merge the accepts only
            static::$pinableSubjects[$subject['as']]['accepts'] = array_merge(
                static::$pinableSubjects[$subject['as']]['accepts'],
                $subject['accepts']
            );

            return;
        }

        static::$pinableSubjects[$subject['as']] = $subject;
    }

    /**
     * Get pinable subject.
     */
    public static function getPinableSubject(string $subject): ?array
    {
        return static::$pinableSubjects[$subject] ?? null;
    }

    /**
     * Get subject accepted timelineable.
     */
    public static function getSubjectAcceptedTimelineable(string $subject, string $timelineableType): ?array
    {
        $accepts = static::getPinableSubject($subject)['accepts'] ?? [];

        return collect($accepts)->firstWhere('as', $timelineableType);
    }
}
