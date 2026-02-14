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
use Modules\Core\Menu\MenuManager;

/**
 * @method static static register(\Modules\Core\Menu\MenuItem|array<\Modules\Core\Menu\MenuItem>|callable $items)
 * @method static static registerItem(\Modules\Core\Menu\MenuItem $item)
 * @method static \Illuminate\Support\Collection<int, \Modules\Core\Menu\MenuItem> get()
 * @method static static metric(\Modules\Core\Menu\Metric|array<\Modules\Core\Menu\Metric> $metric)
 * @method static \Modules\Core\Menu\Metric[] metrics()
 * @method static static clear()
 *
 * @see \Modules\Core\Menu\MenuManager
 */
class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MenuManager::class;
    }
}
