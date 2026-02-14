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

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Modules\Core\Application;
use Modules\Core\Facades\Module as ModuleFacade;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

class ModuleUploader
{
    protected Filesystem $filesystem;

    /**
     * Create new ModuleUploader instance.
     */
    public function __construct(protected UploadedFile $modulesZip)
    {
        $this->filesystem = new Filesystem;
    }

    /**
     * Handle the upload of the module(s).
     */
    public function upload(): array
    {
        $tmpPath = $this->createTempDirectory();
        $moduleTmpPath = $this->createUniqueTempDirectory($tmpPath);

        $this->extractZipTo($moduleTmpPath);

        $moduleJsonFiles = iterator_to_array($this->findModuleJsonFiles($moduleTmpPath));

        if (count($moduleJsonFiles) === 0) {
            throw new ModuleUploadException('No valid modules found in file.', 409);
        }

        $result = $this->processModules($moduleJsonFiles);

        $this->filesystem->deleteDirectory($moduleTmpPath);

        ModuleAutoloader::flushCache();

        return $result;
    }

    /**
     * Create temporary extraction directory.
     */
    protected function createTempDirectory(): string
    {
        $tmpPath = storage_path('modules');
        $this->filesystem->ensureDirectoryExists($tmpPath);

        return $tmpPath;
    }

    protected function createUniqueTempDirectory(string $tmpPath): string
    {
        $tmpFolderName = uniqid();
        $moduleTmpPath = $tmpPath.DIRECTORY_SEPARATOR.$tmpFolderName;
        $this->filesystem->ensureDirectoryExists($moduleTmpPath);

        return $moduleTmpPath;
    }

    /**
     * Extract the uploaded zip to the given destination.
     */
    protected function extractZipTo(string $destination): void
    {
        $zip = new ZipArchive;

        if (true !== ($zip->open($this->modulesZip->getRealPath()))) {
            throw new ModuleUploadException('Cannot open zip archive.', 409);
        }

        if (! $zip->extractTo($destination)) {
            throw new ModuleUploadException('Failed to extract module.', 409);
        }

        $zip->close();
    }

    /**
     * Find the available modules in the extracted directory based on their "json" files location.
     */
    protected function findModuleJsonFiles(string $directory): Finder
    {
        return (new Finder)->in($directory)->files()->name('module.json');
    }

    /**
     * Process the found modules.
     */
    protected function processModules(array $files): array
    {
        $result = [];

        foreach ($files as $file) {
            $moduleFolderName = $file->getRelativePath();
            $moveToPath = config('modules.paths.modules').DIRECTORY_SEPARATOR.$moduleFolderName;

            $module = json_decode($file->getContents(), true);
            $name = $module['name'];

            if ($moduleFolderName === '') {
                $result[$name] = $this->error(
                    'The contents of the module(s) should be placed within its own folder, named after the module itself.'
                );

                continue;
            }

            if (isset($module['requires_at_least']) && ! $this->isMinimumVersionMet($module['requires_at_least'])) {
                $result[$name] = $this->error(
                    sprintf('This module requires at least version %s.', $module['requires_at_least'])
                );

                continue;
            }

            $result[$name] = $this->move($file, $moveToPath, $name);
        }

        return $result;
    }

    /**
     * Create module error response.
     */
    protected function error(string $errorReason): array
    {
        return [
            'success' => false,
            'errorReason' => $errorReason,
        ];
    }

    /**
     * Move the module the the "modules" directory.
     */
    protected function move(SplFileInfo $file, string $moveToPath, string $name): array
    {
        $existed = is_dir($moveToPath);

        $this->filesystem->moveDirectory($file->getPath(), $moveToPath, true);

        if ($existed) {
            $instance = ModuleFacade::find($name);

            if ($instance->isEnabled()) {
                $instance->publishAssets();
            }
        }

        return ['success' => true];
    }

    /**
     * Check if the minuimum application version is met.
     */
    protected function isMinimumVersionMet(string $moduleRequiredVersion): bool
    {
        return version_compare(Application::VERSION, $moduleRequiredVersion, '>=');
    }
}
