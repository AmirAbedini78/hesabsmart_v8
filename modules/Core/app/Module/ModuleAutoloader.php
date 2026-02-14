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

use function Illuminate\Filesystem\join_paths;

class ModuleAutoloader
{
    protected array $autoloadPaths = [];

    protected array $moduleDirs = [];

    protected string $modulesBasePath;

    public function __construct()
    {
        $this->modulesBasePath = realpath(__DIR__.'/../../../../modules');
        $this->scanModulesDir();
        $this->autoloadPaths = $this->loadAutoloadPaths();
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
        $this->autoloadVendorFiles();
    }

    protected function scanModulesDir(): void
    {
        if (is_dir($this->modulesBasePath)) {
            $this->moduleDirs = array_filter(scandir($this->modulesBasePath), function ($dir) {
                return $dir !== '.' && $dir !== '..' && is_dir($this->modulesBasePath.DIRECTORY_SEPARATOR.$dir);
            });
        }
    }

    protected function loadAutoloadPaths(): array
    {
        $cacheFile = static::cacheFile();

        if (file_exists($cacheFile)) {
            return include $cacheFile;
        }

        $autoloadPaths = [];

        foreach ($this->moduleDirs as $moduleDir) {
            $moduleBasePath = $this->modulesBasePath.DIRECTORY_SEPARATOR.$moduleDir;
            $composerFilePath = $moduleBasePath.DIRECTORY_SEPARATOR.'composer.json';

            if (file_exists($composerFilePath)) {
                $composerData = json_decode(file_get_contents($composerFilePath), true);

                if (isset($composerData['autoload']['psr-4'])) {
                    foreach ($composerData['autoload']['psr-4'] as $namespace => $path) {
                        $autoloadPaths[$namespace] = join_paths($moduleBasePath, $path);
                    }
                }
            }
        }

        file_put_contents($cacheFile, '<?php return '.var_export($autoloadPaths, true).';');

        return $autoloadPaths;
    }

    public function autoload(string $class): void
    {
        foreach ($this->autoloadPaths as $namespace => $baseDir) {
            if (strpos($class, $namespace) === 0) {
                $relativeClass = substr($class, strlen($namespace));
                $filePath = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

                if (file_exists($filePath)) {
                    require_once $filePath;
                    break;
                }
            }
        }
    }

    public static function flushCache(): bool
    {
        $cacheFile = static::cacheFile();

        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }

        return false;
    }

    protected static function cacheFile(): string
    {
        return realpath(__DIR__.'/../../../../bootstrap/cache').DIRECTORY_SEPARATOR.'module_autoload.php';
    }

    protected function autoloadVendorFiles(): void
    {
        foreach ($this->moduleDirs as $moduleDir) {
            $vendorAutoload = $this->modulesBasePath.'/'.$moduleDir.'/vendor/autoload.php';

            if (file_exists($vendorAutoload)) {
                require $vendorAutoload;
            }
        }
    }
}
