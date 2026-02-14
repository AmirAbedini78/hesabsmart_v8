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

namespace Modules\Core\Macros;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Localizeable;

trait InteractsWithDates
{
    /**
     * Format the current instance as a string in user's format.
     */
    public function formatForUser(string $format, ?Localizeable $user = null): string
    {
        return $this->inUserTimezone($this, $user)->translatedFormat($format);
    }

    /**
     * Format the current instance date in user's format.
     */
    public function formatDateForUser(?Localizeable $user = null): string
    {
        $user = $this->determineUser($user);

        return $this->formatForUser(
            $user?->getLocalDateFormat() ?? config('core.date_format'),
            $user
        );
    }

    /**
     * Format the current instance time in user's format.
     */
    public function formatTimeForUser(?Localizeable $user = null): string
    {
        $user = $this->determineUser($user);

        return $this->formatForUser(
            $user?->getLocalTimeFormat() ?? config('core.time_format'),
            $user
        );
    }

    /**
     * Format the current instance date and time in user's format.
     */
    public function formatDateTimeForUser(?Localizeable $user = null): string
    {
        return $this->formatDateForUser($user).' '.$this->formatTimeForUser($user);
    }

    /**
     * Display the difference for the current instance in a human-readable format.
     */
    public function diffForHumansForUser(?Localizeable $user = null): string
    {
        return $this->inUserTimezone($this, $user)->diffForHumans();
    }

    /**
     * Convert the Carbon instance to app timezone.
     *
     * E.q. Carbon::asCurrentTimezone()->inAppTimezone();
     */
    public function inAppTimezone(): CarbonInterface
    {
        return $this->timezone(config('app.timezone'));
    }

    /**
     * 1. If date provided: Takes the given UTC date and converts to the current timezone
     * 2. If date not provided: Uses the current (now) UTC date
     * 3. If $user not provided: Uses current logged-in user
     * 4. If no logged in user: uses application timezone, equal to no.2
     *
     * Usually using in where queries when querying data from database when
     * the query dates must be converted from utc to user timezone and then to to application timezone
     * so we can know the exact UTC date of the user timezone
     *
     * For example, we have the 2021-12-15 15:00:00 in UTC date
     * but the logged in user uses America/New_York date, we must convert the UTC date to New York date
     * and then to application date so we can know the current New York in UTC date
     *
     * This is how timezoned dates should work we believe, we have UTC date, we have Timezone
     * we use the UTC date and the Timezone to convert the UTC date to the Timezone and then
     * the converted date can be easily converted to UTC (config('app.timezone')) using the inAppTimezone method
     *
     * The method should be used also for dates manipulations
     * e.q.
     *
     * Carbon::asCurrentTimezone($utcdate)
     *            ->subMinutes(30)
     *           ->inAppTimezone();
     *
     * Also is equivalent to:
     *
     * Carbon::createFromFormat(
     *    'Y-m-d H:i:s',
     *    '2021-12-15 00:00:00',
     *    'America/New_york'
     * );
     *
     * @see forAppTimezone
     * @see inAppTimezone
     */
    public static function asCurrentTimezone(mixed $time = null, ?Localizeable $user = null): CarbonInterface
    {
        return static::parse($time, tz()->current($user));
    }

    /**
     * Convert the given UTC date be used for the application
     * e.q. UTC date to New_York to UTC, in this case
     * we will know the New_York date in UTC.
     *
     * @return \Carbon\Carbon
     */
    public static function fromCurrentToAppTimezone(mixed $time, ?Localizeable $user = null): CarbonInterface
    {
        return static::asCurrentTimezone($time, $user)->inAppTimezone();
    }

    /**
     * Get date from the current timezone as in app timezone.
     */
    public static function asAppTimezone(mixed $time = null, ?Localizeable $user = null): CarbonInterface
    {
        return static::asCurrentTimezone($time, $user)->inAppTimezone();
    }

    /**
     * Get the timestamp in user timezone.
     */
    public static function inUserTimezone(mixed $time, ?Localizeable $user = null): CarbonInterface
    {
        $timezone = tz()->current($user);

        return static::parse($time)->timezone($timezone);
    }

    /**
     * Determine the user based on the optional user parameter or the authenticated user.
     */
    protected function determineUser(?Localizeable $user): ?Localizeable
    {
        return $user ?: Auth::user();
    }
}
