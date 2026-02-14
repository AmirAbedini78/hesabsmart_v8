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

namespace App\Foundation;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Filesystem\Filesystem;

class CustomApplication extends Application
{
    protected function registerBaseBindings()
    {
        parent::registerBaseBindings();

        $this->singleton(PackageManifest::class, fn () => new CustomPackageManifest(
            new Filesystem, $this->app->basePath(), $this->app->getCachedPackagesPath()
        ));
    }
}