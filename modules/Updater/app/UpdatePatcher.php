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

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class UpdatePatcher
{
    /**
     * Run the patcher.
     */
    abstract public function run(): void;

    /**
     * Check whether the patcher should run.
     */
    abstract public function shouldRun(): bool;

    /**
     * Get the version number this patcher is intended for.
     */
    public function version(): string
    {
        $versionFromFilename = $this->versionFromFilename();

        // semver
        if (str_contains($versionFromFilename, '.')) {
            return $versionFromFilename;
        }

        // 110 => 1.1.0
        return wordwrap($this->versionFromFilename(), 1, '.', true);
    }

    /**
     * Get the version from the filename.
     */
    public function versionFromFilename(): string
    {
        return Str::after($this->filenameWithoutExtension(), 'Update');
    }

    /**
     * Get the class filename without extension.
     */
    protected function filenameWithoutExtension(): string
    {
        return str_replace('.php', '', basename((new ReflectionClass($this))->getFileName()));
    }

    /**
     * Get indexes for specific column.
     */
    protected function getColumnIndexes(string $table, string $column): array
    {
        return Schema::getIndexesForColumn($table, $column);
    }
}
