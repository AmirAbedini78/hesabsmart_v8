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

namespace Modules\Installer;

use Modules\Core\Application;

class Installer
{
    const ROUTE_PREFIX = 'install';

    const INSTALLED_FILE = '.installed';

    const BASE_PATH = __DIR__.'/../../..';

    public function markAsInstalled(): bool
    {
        if (file_exists(static::installedFileLocation())) {
            return true;
        }

        $bytes = file_put_contents(
            static::installedFileLocation(),
            'Installation Date: '.date('Y-m-d H:i:s').PHP_EOL.'Version: '.Application::VERSION
        );

        return $bytes !== false;
    }

    public static function isAppInstalled(): bool
    {
        return file_exists(static::installedFileLocation());
    }

    public static function installedFileLocation(): string
    {
        return storage_path(static::INSTALLED_FILE);
    }

    public static function isInstalling(): bool
    {
        return strpos($_SERVER['REQUEST_URI'], static::ROUTE_PREFIX) !== false;
    }

    public static function requiresInstallation(): bool
    {
        return ! file_exists(implode(DIRECTORY_SEPARATOR, [
            static::BASE_PATH,
            'storage',
            static::INSTALLED_FILE,
        ]));
    }
}
