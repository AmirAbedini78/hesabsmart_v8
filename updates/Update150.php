<?php

use App\ToModuleMigrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Core\Facades\Module as ModuleFacade;
use Modules\Core\Macros\DeepCleanDirectory;
use Modules\Updater\UpdatePatcher;
use Nwidart\Modules\Laravel\Module;
use Symfony\Component\Finder\Finder;

return new class extends UpdatePatcher
{
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = new Filesystem;
    }

    public function run(): void
    {
        /** @var array<array-key, Module> */
        $modules = ModuleFacade::core();

        (new DeepCleanDirectory)->__invoke(base_path('database/migrations'), true, [
            base_path('database/migrations/2019_08_19_000000_create_failed_jobs_table.php'),
            base_path('database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php'),
            base_path('database/migrations/2020_06_08_174707_create_cache_table.php'),
            base_path('database/migrations/2020_06_10_160037_create_jobs_table.php'),
            base_path('database/migrations/2022_08_30_154952_create_views_table.php'),
            base_path('database/migrations/2024_03_31_084617_create_cache_locks_table.php'),
            base_path('database/migrations/2024_03_31_084706_create_job_batches_table.php'),
        ]);

        foreach ($modules as $module) {
            $notifications = $this->getNotifications($module);
            $mailableTemplates = $this->getMailableTemplates($module);
            $workflowActions = $this->getWorkflowActions($module);
            $workflowTriggers = $this->getWorkflowTriggers($module);

            $migrator = ToModuleMigrator::make($module->getLowerName())
                ->migrateMailableTemplates(array_combine($this->toOldNamespace($mailableTemplates, $module), $mailableTemplates))
                ->migrateNotifications(array_combine($this->toOldNamespace($notifications, $module), $notifications))
                ->migrateWorkflowActions(array_combine($this->toOldNamespace($workflowActions, $module), $workflowActions))
                ->migrateWorkflowTriggers(array_combine($this->toOldNamespace($workflowTriggers, $module), $workflowTriggers));

            foreach ($this->getModels($module) as $newModel) {
                $migrator->migrateMorphs($this->toOldNamespace([$newModel], $module)[0], $newModel);
            }

            $this->migrateDirectories($module);
        }

        $this->migrateMailHeaders();

        $this->files->delete(config_path('installer.php'));
        $this->files->delete(module_path('Core', 'app/Fields/MorphMany.php'));
        $this->files->delete(module_path('Core', 'app/Fields/MorphToMany.php'));
        $this->files->delete(module_path('Core', 'app/Fields/HasMany.php'));
        $this->files->delete(module_path('Core', 'app/Models/Filter.php'));
        $this->files->delete(module_path('Core', 'app/Models/Metable.php'));
        $this->files->delete(module_path('Core', 'app/Models/UserOrderedModel.php'));
        $this->files->delete(module_path('Core', 'app/Models/FilterDefaultView.php'));
        $this->files->deepCleanDirectory(app_path('Http/Controllers/Auth'), false);
        $this->files->deepCleanDirectory(app_path('Installer'), false);
        $this->files->deepCleanDirectory(app_path('Support'), false);
        $this->files->deepCleanDirectory(app_path('Exceptions'), false);

        if (Schema::hasTable('user_ordered_models')) {
            foreach (DB::table('user_ordered_models')->get() as $data) {
                DB::table('user_sorted_models')->insert([
                    'display_order' => $data->display_order,
                    'user_id' => $data->user_id,
                    'sortable_type' => $data->orderable_type,
                    'sortable_id' => $data->orderable_id,
                ]);
            }

            DB::table('user_ordered_models')->delete();
            Schema::drop('user_ordered_models');
        }

        DB::table('migrations')
            ->where('migration', '2022_06_21_134625_create_user_ordered_models_table')
            ->delete();

        $this->files->delete(
            module_path('Core', 'database/migrations/2022_06_21_134625_create_user_ordered_models_table.php')
        );
    }

    protected function migrateDirectories(Module $module): void
    {
        $renameOldPaths = [];

        $directories = [
            'app',
            'database',
            'tests',
        ];

        foreach (ModuleFacade::core() as $module) {
            $name = $module->getName();
            $renameOldPaths[$name] = [];

            foreach ($directories as $directory) {
                if (is_dir(module_path($name, ucfirst($directory)))) {
                    $renameOldPaths[$name][$directory] = [
                        'has_new_dir' => false,
                        'has_old_dir' => false,
                    ];

                    foreach ($this->files->directories(module_path($name)) as $onDiskDir) {
                        if (basename($onDiskDir) === ucfirst($directory)) {
                            $renameOldPaths[$name][$directory]['has_old_dir'] = true;
                        }

                        if (basename($onDiskDir) === $directory) {
                            $renameOldPaths[$name][$directory]['has_new_dir'] = true;
                        }
                    }
                }
            }
        }

        foreach ($renameOldPaths as $moduleName => $dirs) {
            foreach ($dirs as $dir => $info) {
                if (! $info['has_new_dir'] && $info['has_old_dir']) {
                    $this->files->moveDirectory(
                        module_path($moduleName, ucfirst($dir)),
                        module_path($moduleName, $dir)
                    );
                } elseif ($info['has_new_dir'] && $info['has_old_dir']) {
                    $this->files->moveDirectory(
                        module_path($moduleName, ucfirst($dir)),
                        module_path($moduleName, ucfirst($dir).'-old')
                    );
                }
            }
        }

        $this->migrateDatabaseDirectories();
        $this->removeOldDirectories();
    }

    protected function migrateDatabaseDirectories()
    {
        foreach (['State', 'Migrations', 'Factories', 'Seeders'] as $dirToMigrate) {
            $renameOldMigrations = [];

            foreach (ModuleFacade::core() as $module) {
                $name = $module->getName();

                if (is_dir($dbPath = module_path($name, 'database'))) {
                    $renameOldMigrations[$name] = [
                        'has_new_dir' => false,
                        'has_old_dir' => false,
                    ];

                    foreach ($this->files->directories($dbPath) as $dir) {
                        if (basename($dir) === $dirToMigrate) {
                            $renameOldMigrations[$name]['has_old_dir'] = true;
                        }

                        if (basename($dir) === strtolower($dirToMigrate)) {
                            $renameOldMigrations[$name]['has_new_dir'] = true;
                        }
                    }
                }
            }

            foreach ($renameOldMigrations as $name => $data) {
                if (! $data['has_new_dir'] && $data['has_old_dir']) {
                    $this->files->moveDirectory(
                        module_path($name, 'database/'.$dirToMigrate),
                        module_path($name, 'database/'.strtolower($dirToMigrate))
                    );
                } elseif ($data['has_new_dir'] && $data['has_old_dir']) {
                    $this->files->moveDirectory(
                        module_path($name, 'database/'.$dirToMigrate),
                        module_path($name, 'database/'.$dirToMigrate.'-old')
                    );
                }
            }
        }
    }

    public function removeOldDirectories()
    {
        $except = [
            'app', 'config', 'database', 'lang', 'resources', 'routes', 'tests',
            'updates', '.gitignore', 'composer.json', 'module.json', 'package.json', 'vite.config.js',
            '.gitignore', '_old',
        ];

        foreach (ModuleFacade::core() as $module) {
            $modulePath = module_path($module->getName());
            $moveTo = $modulePath.DIRECTORY_SEPARATOR.'_old';

            $finder = new Finder;

            $finder->in($modulePath)->depth('== 0');

            foreach ($except as $exclude) {
                $finder->notName($exclude);
                $finder->notPath($exclude);
            }

            if (count($finder) > 0 && ! $this->files->exists($moveTo)) {
                $this->files->makeDirectory($moveTo, 0755, true);
            }

            foreach ($finder as $file) {
                if ($file->isDir()) {
                    $this->files->copyDirectory($file->getRealPath(), $moveTo.DIRECTORY_SEPARATOR.$file->getBasename());
                    $this->files->deepCleanDirectory($file->getRealPath());
                    $this->files->deleteDirectory($file->getRealPath());
                } else {
                    $this->files->move($file->getRealPath(), $moveTo.DIRECTORY_SEPARATOR.$file->getFilename());
                }
            }
        }
    }

    protected function getMailableTemplates(Module $module): array
    {
        return $this->filesToNamespace(
            $this->retreiveFiles($module->getAppPath().DIRECTORY_SEPARATOR.'Mail')
        );
    }

    protected function getModels(Module $module): array
    {
        return $this->filesToNamespace(
            $this->retreiveFiles($module->getAppPath().DIRECTORY_SEPARATOR.'Models')
        );
    }

    protected function getNotifications(Module $module): array
    {
        return $this->filesToNamespace(
            $this->retreiveFiles($module->getAppPath().DIRECTORY_SEPARATOR.'Notifications')
        );
    }

    protected function getWorkflowActions(Module $module): array
    {
        return $this->filesToNamespace(
            $this->retreiveFiles($module->getAppPath().DIRECTORY_SEPARATOR.'Workflow'.DIRECTORY_SEPARATOR.'Actions')
        );
    }

    protected function getWorkflowTriggers(Module $module): array
    {
        return $this->filesToNamespace(
            $this->retreiveFiles($module->getAppPath().DIRECTORY_SEPARATOR.'Workflow'.DIRECTORY_SEPARATOR.'Triggers')
        );
    }

    protected function retreiveFiles(string $dir): array
    {
        if (! $this->files->isDirectory($dir)) {
            return [];
        }

        return $this->files->allFiles($dir);
    }

    protected function toOldNamespace(array $namespaces, Module $module): array
    {
        $new = [];

        foreach ($namespaces as $namespace) {
            $new[] = Str::replaceFirst(
                'Modules\\'.$module->getStudlyName(),
                'Modules\\'.$module->getStudlyName().'\\App',
                $namespace
            );
        }

        return $new;
    }

    protected function filesToNamespace(array $files, $rootNamespace = 'Modules', $excludeDir = 'app'): array
    {
        $baseDir = base_path('modules');

        $namespaces = [];

        foreach ($files as $file) {

            $filePath = $file->getRealPath();

            // Normalize directory separators
            $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
            $baseDir = str_replace('/', DIRECTORY_SEPARATOR, $baseDir);

            // Ensure baseDir ends with a directory separator for accurate replacement
            if (substr($baseDir, -1) != DIRECTORY_SEPARATOR) {
                $baseDir .= DIRECTORY_SEPARATOR;
            }

            // Remove the base directory from the file path
            if (substr($filePath, 0, strlen($baseDir)) == $baseDir) {
                $relativeClassPath = substr($filePath, strlen($baseDir));
            } else {
                // The file path does not contain the base directory
                return null;
            }

            // Optionally remove specific subdirectory from the namespace path
            if (! empty($excludeDir)) {
                $relativeClassPath = str_replace($excludeDir.DIRECTORY_SEPARATOR, '', $relativeClassPath);
            }

            // Remove the '.php' extension
            $className = rtrim($relativeClassPath, '.php');

            // Convert directory separators to namespace separators
            $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $className);

            // Add root namespace if provided
            if (! empty($rootNamespace)) {
                $namespace = rtrim($rootNamespace, '\\').'\\'.ltrim($namespace, '\\');
            }

            // Remove any leading backslash if root namespace is not provided
            if (empty($rootNamespace)) {
                $namespace = ltrim($namespace, '\\');
            }
            $namespaces[] = $namespace;
        }

        return $namespaces;
    }

    protected function migrateMailHeaders(): void
    {
        foreach ([
            'Modules\\Core\\Common\\Mail\\Headers\\AddressHeader' => [
                'Modules\\Core\\App\\Support\\Mail\\Headers\\AddressHeader',
                'Modules\\Core\\Support\\Mail\\Headers\\AddressHeader',
                'Modules\\Core\\Mail\\Headers\\AddressHeader',
                'Modules\\Core\\App\\Mail\\Headers\\AddressHeader',
                'Modules\\Core\\App\\Common\\Mail\\Headers\\AddressHeader',
            ],
            'Modules\\Core\\Common\\Mail\\Headers\\DateHeader' => [
                'Modules\\Core\\App\\Support\\Mail\\Headers\\DateHeader',
                'Modules\\Core\\Mail\\Headers\\DateHeader',
                'Modules\\Core\\Support\\Mail\\Headers\\DateHeader',
                'Modules\\Core\\App\\Mail\\Headers\\DateHeader',
                'Modules\\Core\\App\\Common\\Mail\\Headers\\DateHeader',
            ],
            'Modules\\Core\\Common\\Mail\\Headers\\IdHeader' => [
                'Modules\\Core\\App\\Support\\Mail\\Headers\\IdHeader',
                'Modules\\Core\\Mail\\Headers\\IdHeader',
                'Modules\\Core\\Support\\Mail\\Headers\\IdHeader',
                'Modules\\Core\\App\\Mail\\Headers\\IdHeader',
                'Modules\\Core\\App\\Common\\Mail\\Headers\\IdHeader',
            ],
            'Modules\\Core\\Common\\Mail\\Headers\\Header' => [
                'Modules\\Core\\App\\Support\\Mail\\Headers\\Header',
                'Modules\\Core\\Mail\\Headers\\Header',
                'Modules\\Core\\Support\\Mail\\Headers\\Header',
                'Modules\\Core\\App\\Mail\\Headers\\Header',
                'Modules\\Core\\App\\Common\\Mail\\Headers\\Header',
            ],
        ] as $newHeader => $oldHeaders) {
            DB::table('email_account_message_headers')->whereIn('header_type', $oldHeaders)->update([
                'header_type' => $newHeader,
            ]);
        }
    }

    public function shouldRun(): bool
    {
        return true;
    }
};
