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

/**
 * @method static ?string getSiteKey()
 * @method static static setSiteKey(?string $key)
 * @method static ?string getSecretKey()
 * @method static static setSecretKey(?string $key)
 * @method static array getSkippedIps()
 * @method static static setSkippedIps(array|string $ips)
 * @method static bool shouldShow(?string $ip = null)
 * @method static bool shouldSkip(?string $ip = null)
 * @method static bool configured()
 *
 * @see \Modules\Core\Support\ReCaptcha
 */
class ReCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'recaptcha';
    }
}
