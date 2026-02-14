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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\Timezone as CoreTimezone;

/**
 * @method static string convertFromUTC(string $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s')
 * @method static string convertToUTC(string $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s')
 * @method static string fromUTC(string $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s')
 * @method static string toUTC(string $timestamp, ?string $timezone = null, string $format = 'Y-m-d H:i:s')
 * @method static string current(?\Modules\Core\Contracts\Localizeable $user = null)
 * @method static array all()
 * @method static array toArray()
 *
 * @see \Modules\Core\Timezone
 */
class Timezone extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return CoreTimezone::class;
    }
}
