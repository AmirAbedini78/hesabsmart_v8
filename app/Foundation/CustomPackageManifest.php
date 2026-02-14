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

use Illuminate\Foundation\PackageManifest;

class CustomPackageManifest extends PackageManifest
{
    protected function packagesToIgnore()
    {
        $packagesToIgnore = parent::packagesToIgnore();

        $packagesToIgnore = array_merge($packagesToIgnore, $this->getModulePackagesToIgnore());

        return $packagesToIgnore;
    }

    private function getModulePackagesToIgnore(): array
    {
        $modulesPath = base_path('modules');
        $packagesToIgnore = [];

        $moduleDirs = array_filter(glob($modulesPath . '/*'), 'is_dir');

        foreach ($moduleDirs as $moduleDir) {
            $composerPath = "{$moduleDir}/composer.json";

            if (file_exists($composerPath)) {
                $composerData = json_decode(file_get_contents($composerPath), true);

                if (isset($composerData['extra']['laravel']['dont-discover'])) {
                    $packagesToIgnore = array_merge(
                        $packagesToIgnore,
                        $composerData['extra']['laravel']['dont-discover']
                    );
                }
            }
        }

        $packagesToIgnore = array_unique($packagesToIgnore);

        return $packagesToIgnore;
    }
}