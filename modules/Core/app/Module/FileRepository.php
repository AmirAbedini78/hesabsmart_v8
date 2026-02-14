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

namespace Modules\Core\Module;

use Nwidart\Modules\Json;
use Nwidart\Modules\Laravel\LaravelFileRepository;

class FileRepository extends LaravelFileRepository
{
    protected static $discoveredModules = [];

    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new Module(...$args);
    }

    /**
     * Retrieve and scan all modules.
     *
     * This function is identical to the parent implementation, with the following modifications:
     * - Caching is enabled for discovered modules during unit tests to improve performance,
     *   as scanning for modules significantly slows down unit tests.
     * - Introduced a new "discoveredModules" property (since the original "modules" property is private).
     *
     * Note:
     * - No modules are created during unit tests, so caching does not risk discovered modules being outdated.
     *
     * @return array The list of discovered modules.
     */
    public function scan()
    {
        if (! empty(self::$discoveredModules) && ! $this->app->runningInConsole()) {
            return self::$discoveredModules;
        }

        $paths = $this->getScanPaths();

        $modules = [];

        foreach ($paths as $key => $path) {
            $manifests = $this->getFiles()->glob("{$path}/module.json");

            is_array($manifests) || $manifests = [];

            foreach ($manifests as $manifest) {
                $json = Json::make($manifest);
                $name = $json->get('name');

                $modules[strtolower($name)] = $this->createModule($this->app, $name, dirname($manifest));
            }
        }

        self::$discoveredModules = $modules;

        return self::$discoveredModules;
    }
}
