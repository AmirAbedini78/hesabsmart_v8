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
use Modules\Core\Common\Google\Client;

/**
 * @method static static connectUsing(string|\Modules\Core\Common\OAuth\AccessTokenProvider)
 * @method static \Modules\Core\Common\Google\Services\Message message()
 * @method static \Modules\Core\Common\Google\Services\Labels labels()
 * @method static \Modules\Core\Common\Google\Services\History history()
 * @method static \Modules\Core\Common\Google\Services\Calendar calendar()
 * @method static void revokeToken(?string $accessToken = null)
 * @method static \Google\Client getClient()
 *
 * @see \Modules\Core\Common\Google\Client
 */
class Google extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
