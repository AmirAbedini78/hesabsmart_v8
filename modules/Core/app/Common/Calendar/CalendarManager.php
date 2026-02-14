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

namespace Modules\Core\Common\Calendar;

use InvalidArgumentException;
use Modules\Core\Common\Calendar\Google\GoogleCalendar;
use Modules\Core\Common\Calendar\Outlook\OutlookCalendar;
use Modules\Core\Common\OAuth\AccessTokenProvider;
use Modules\Core\Contracts\OAuth\Calendarable;

class CalendarManager
{
    /**
     * Create calendar client.
     */
    public static function createClient(string $connectionType, AccessTokenProvider $token): Calendarable
    {
        $method = 'create'.ucfirst($connectionType).'Driver';

        if (! method_exists(new static, $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve [%s] driver for [%s].',
                $method,
                static::class
            ));
        }

        return self::$method($token);
    }

    /**
     * Create the Google calendar driver.
     */
    public static function createGoogleDriver(AccessTokenProvider $token): GoogleCalendar&Calendarable
    {
        return new GoogleCalendar($token);
    }

    /**
     * Create the Outlook calendar driver.
     */
    public static function createOutlookDriver(AccessTokenProvider $token): OutlookCalendar&Calendarable
    {
        return new OutlookCalendar($token);
    }
}
