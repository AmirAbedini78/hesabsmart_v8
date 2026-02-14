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

use Illuminate\Database\Migrations\Migrator as LaravelMigrator;
use Illuminate\Support\Facades\Artisan;

class DatabaseMigrator
{
    /**
     * Initialize new Migration instance
     */
    public function __construct(protected LaravelMigrator $migrator) {}

    /**
     * Run the application migrations.
     */
    public function run(): void
    {
        $this->prepareDatabase();

        $this->migrator->run($this->getAllMigrationFiles());
    }

    /**
     * Check whether the application requires migrations to be run.
     */
    public function needed(): bool
    {
        $ran = $this->migrator->getRepository()->getRan();
        $all = $this->getAllMigrationFiles();

        if (count($all) > 0) {
            return count($all) > count($ran);
        }

        return false;
    }

    /**
     * Get an array of all of the migration files.
     */
    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get all of the migration paths.
     */
    protected function getMigrationPaths(): array
    {
        return array_merge(
            $this->migrator->paths(),
            [$this->getBaseMigrationPath()]
        );
    }

    /**
     * Get the path to the base migration directory.
     */
    protected function getBaseMigrationPath(): string
    {
        return database_path('migrations');
    }

    /**
     * Prepare the migration database for running.
     *
     * @see "MigrateCommand.php"
     */
    protected function prepareDatabase(): void
    {
        if (! $this->repositoryExists()) {
            Artisan::call('migrate:install');
        }
    }

    /**
     * Determine if the migrator repository exists.
     */
    protected function repositoryExists(): bool
    {
        return retry(2, $this->migrator->repositoryExists(...), 0);
    }
}
