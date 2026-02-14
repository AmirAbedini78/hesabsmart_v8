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
use Modules\Core\Settings\SettingsMenu as SettingsMenuManager;

/**
 * @method static void register(\Modules\Core\Settings\SettingsMenuItem|callable|array $item)
 * @method static void add(string $id, \Modules\Core\Settings\SettingsMenuItem|callable|array $item)
 * @method static ?\Modules\Core\Settings\SettingsMenuItem find(string $id)
 * @method static array<int,\Modules\Core\Settings\SettingsMenuItem> all()
 *
 * @see \Modules\Core\Settings\SettingsMenu
 */
class SettingsMenu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return SettingsMenuManager::class;
    }
}
