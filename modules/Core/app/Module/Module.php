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

use Illuminate\Container\Container;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Laravel\Module as BaseModule;

class Module extends BaseModule
{
    /**
     * @var ModuleBootstrapper[]
     */
    protected static array $bootstrappers = [];

    protected string $bootstrapDirectory = 'bootstrap';

    protected string $bootstrapFilename = 'module.php';

    public function __construct(Container $app, string $name, $path)
    {
        parent::__construct($app, $name, $path);

        $this->bootWithBootstrapFile();
    }

    public function assetsPath(): string
    {
        return public_path('modules/'.$this->getLowerName());
    }

    public function publishAssets(): void
    {
        if (is_dir($dir = $this->getExtraPath('public'))) {
            $this->unpublishAssets();

            $this->app['files']->copyDirectory($dir, $this->assetsPath());
        }
    }

    public function unpublishAssets(): void
    {
        if (is_dir($assetsPath = $this->assetsPath())) {
            $this->app['files']->deepCleanDirectory($assetsPath, false);
            $this->app['files']->deleteDirectory($assetsPath);
        }
    }

    public static function upload(UploadedFile $file): array
    {
        return (new ModuleUploader($file))->upload();
    }

    public function enable(): void
    {
        $this->publishAssets();

        parent::enable();
    }

    public function disable(): void
    {
        $this->unpublishAssets();

        parent::disable();
    }

    public function registerBroadcastChannels()
    {
        if (file_exists($this->getExtraPath('routes/channels.php'))) {
            require_once $this->getExtraPath('routes/channels.php');
        }
    }

    public function isCore(): bool
    {
        return in_array($this->getName(), \DetachedHelper::CORE_MODULES);
    }

    public function version(): ?string
    {
        return $this->get('version');
    }

    public function bootstrapFileLocation()
    {
        return $this->getExtraPath($this->bootstrapDirectory.'/'.$this->bootstrapFilename);
    }

    public function bootWithBootstrapFile(): void
    {
        // Only include the file
        $this->bootstrapper();
    }

    public function hasBootstrapFile(): bool
    {
        return $this->app['files']->exists($this->bootstrapFileLocation());
    }

    public function bootstrapper(): ?ModuleBootstrapper
    {
        if (! $this->hasBootstrapFile()) {
            return null;
        }

        return static::$bootstrappers[$this->getLowerName()] ??= $this->app['files']->getRequire(
            $this->bootstrapFileLocation()
        );
    }

    public function purge()
    {
        $this->fireEvent('deleting');

        if ($this->bootstrapper()?->resetsMigrations()) {
            Artisan::call('module:migrate-reset', [
                'module' => $this->getLowerName(),
                '--force' => true,
            ]);
        }

        $this->unpublishAssets();
        $this->delete();

        $this->fireEvent('deleted');
    }
}
