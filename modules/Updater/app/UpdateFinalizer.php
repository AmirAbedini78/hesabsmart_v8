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

namespace Modules\Updater;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Modules\Core\Application;
use Modules\Core\Environment;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\Module;
use Modules\Updater\Events\UpdateFinalized;
use SplFileInfo;

class UpdateFinalizer
{
    protected static array $before = [];

    /**
     * The directory name where the updates patchers are stored.
     */
    protected string $updatersDir = 'updates';

    /**
     * Run the update finalizer.
     */
    public function run(): bool
    {
        if (! $this->needed()) {
            return false;
        }

        Innoclapps::clearCache();

        $this->runUpdaters();

        Environment::setUpdateDate(date('Y-m-d H:i:s'));

        settings([
            '_version' => $version = Application::VERSION,
            '_updated_from' => $updatedFrom = $this->getCachedCurrentVersion(),
        ]);

        UpdateFinalized::dispatch($version, $updatedFrom);

        $this->optimizeIfNeeded();

        if (config('updater.restart_queue')) {
            Innoclapps::restartQueue();
        }

        return true;
    }

    /**
     * Check whether finalization of the update is needed.
     */
    public function needed(): bool
    {
        return version_compare($this->getCachedCurrentVersion(), Application::VERSION, '<');
    }

    /**
     * Get the cached current version.
     */
    public function getCachedCurrentVersion(): string
    {
        return settings('_version') ?: ($_SERVER['_VERSION'] ?? '1.0.7');
    }

    /**
     * Optimize the application.
     */
    protected function optimizeIfNeeded(): void
    {
        if (! app()->runningUnitTests() && app()->isProduction()) {
            Innoclapps::optimize();
        }
    }

    /**
     * Get all of the update patcher instances.
     */
    protected function updaters(): Collection
    {
        $filesystem = new Filesystem;

        return collect($filesystem->files(base_path($this->updatersDir)))
            ->when(
                Module::allEnabled(),
                fn ($collection) => $collection->push(
                    ...$this->findModulesUpdaters($filesystem)
                )
            )
            ->filter(
                fn (SplFileInfo $file) => str_ends_with($file->getRealPath(), '.php') &&
                     str_starts_with($file->getFilename(), 'Update')
            )
            ->values()
            ->map(fn (SplFileInfo $file) => $filesystem->getRequire($file->getRealPath()))
            ->sortBy(
                fn (UpdatePatcher $patch) => $patch->version()
            )
            ->values();
    }

    /**
     * Get all of the applicable updates patchers for the current version.
     */
    protected function updatersForCurrentVersion(): Collection
    {
        return $this->updaters()
            // Get all the versions starting from current cached (excluding current cached as this one is already executed)
            // between the latest available update for the current version (including current)
            ->filter(
                fn ($patch) => ! (version_compare($patch->version(), $this->getCachedCurrentVersion(), '<=') ||
                    version_compare($patch->version(), Application::VERSION, '>'))
            );
    }

    /**
     * Find all of the updates patchers classes from modules.
     */
    protected function findModulesUpdaters(Filesystem $filesystem): array
    {
        $files = [];

        foreach (Module::allEnabled() as $module) {
            $path = module_path($module->getLowerName(), $this->updatersDir);

            if ($filesystem->isDirectory($path)) {
                $files = [...$files, ...$filesystem->files($path)];
            }
        }

        return $files;
    }

    /**
     * Execute the updates patchers.
     */
    protected function runUpdaters(): void
    {
        $this->updatersForCurrentVersion()->filter->shouldRun()->each->run();
    }
}
