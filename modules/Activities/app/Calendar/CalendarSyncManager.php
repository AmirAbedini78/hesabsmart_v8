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

use InvalidArgumentException;
use Modules\Activities\Models\Calendar;

class CalendarSyncManager
{
    /**
     * Create calendar synchronizer
     *
     * @return \Modules\Activities\Calendar\CalendarSynchronization&\Modules\Core\Contracts\Synchronization\Synchronizable
     */
    public static function createClient(Calendar $calendar)
    {
        $method = 'create'.ucfirst($calendar->connection_type).'Driver';

        if (! method_exists(new static, $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve [%s] driver for [%s].',
                $method,
                static::class
            ));
        }

        return self::$method($calendar);
    }

    /**
     * Create the Google calendar sync driver
     *
     *
     * @return \Modules\Activities\Calendar\GoogleCalendarSync
     */
    public static function createGoogleDriver(Calendar $calendar)
    {
        return new GoogleCalendarSync($calendar);
    }

    /**
     * Create the Outlook calendar sync driver
     *
     *
     * @return \Modules\Activities\Calendar\OutlookCalendarSync
     */
    public static function createOutlookDriver(Calendar $calendar)
    {
        return new OutlookCalendarSync($calendar);
    }
}
