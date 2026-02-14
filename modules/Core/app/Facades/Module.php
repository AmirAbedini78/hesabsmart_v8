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

use Modules\Core\Module\ModuleBootstrapper;
use Nwidart\Modules\Facades\Module as NwidartModuleFacade;

class Module extends NwidartModuleFacade
{
    /**
     * Register module bootstrapping.
     */
    public static function configure(string $module): ModuleBootstrapper
    {
        return new ModuleBootstrapper($module);
    }
}
