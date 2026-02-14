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
use Modules\Core\Permissions as PermissionsManager;

/**
 * @method static void group(string|array $group, \Closure $callback)
 * @method static array groups()
 * @method static void view(string $view, array $data)
 * @method static void createMissing()
 * @method static array all()
 * @method static array labeled()
 * @method static void register(\Closure $callback)
 *
 * @see \Modules\Core\Permissions
 */
class Permissions extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PermissionsManager::class;
    }
}
