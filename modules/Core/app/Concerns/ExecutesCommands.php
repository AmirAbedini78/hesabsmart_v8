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

namespace Modules\Core\Concerns;

use Illuminate\Support\Facades\Artisan;

trait ExecutesCommands
{
    /**
     * Create storage symbolic link.
     */
    public static function createStorageLink(bool $force = true): void
    {
        static::runCommand('storage:link', ['--force' => $force]);
    }

    /**
     * Optimize the application.
     */
    public static function optimize(): void
    {
        static::runCommands(
            (array) config('core.commands.optimize', 'optimize')
        );
    }

    /**
     * Clear the application cache.
     */
    public static function clearCache(): void
    {
        static::runCommands(
            (array) config('core.commands.clear-cache', 'optimize:clear')
        );
    }

    /**
     * Restart the queue (if configured).
     */
    public static function restartQueue(): void
    {
        try {
            static::runCommand('queue:restart');
        } catch (\Exception) {
        }
    }

    /**
     * Execute an array of commands.
     */
    public static function runCommands(array $commands): void
    {
        foreach ($commands as $command) {
            static::runCommand($command);
        }
    }

    /**
     * Execute the given command.
     */
    public static function runCommand(string|array|null $command, array|string $params = []): mixed
    {
        if (! $command) {
            return false;
        }

        if (is_array($command)) {
            $name = $command[0];
            $params = $command[1] ?? [];
        }

        return Artisan::call($name ?? $command, $params);
    }
}
