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

namespace Modules\Core;

use Illuminate\Support\Facades\DB;

class Environment
{
    /**
     * Get the database driver name.
     */
    public static function getDatabaseDriver(): ?string
    {
        return DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Get the database driver version.
     */
    public static function getDatabaseDriverVersion(): ?string
    {
        return DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Get the captured server IP address.
     */
    public static function getServerIp(): ?string
    {
        return $_SERVER['SERVER_ADDR'] ?? '';
    }

    /**
     * Capture the installation date.
     */
    public static function setInstallationDate(?string $date = null): void
    {
        settings(['_installed_date' => $date ?: date('Y-m-d H:i:s')]);
    }

    /**
     * Get the installation date.
     */
    public static function getInstallationDate(): ?string
    {
        return settings('_installed_date');
    }

    /**
     * Capture the last update date.
     */
    public static function setUpdateDate(?string $date = null): void
    {
        settings(['_last_updated_date' => $date ?: date('Y-m-d H:i:s')]);
    }

    /**
     * Get the last update date.
     */
    public static function getUpdateDate(): ?string
    {
        return settings('_last_updated_date');
    }

    /**
     * Capture the current environment in storage.
     */
    public static function capture(array $extra = []): void
    {
        settings(array_merge([
            '_env_captured_at' => now()->toISOString(), // mostly used for tests
            '_app_url' => config('app.url'),
            '_prev_app_url' => settings('_app_url'),
            '_server_ip' => static::getServerIp(), // may not be always reliable
            '_server_hostname' => gethostname() ?: '',
            '_php_version' => PHP_VERSION,
            '_db_driver' => static::getDatabaseDriver(),
            '_db_driver_version' => static::getDatabaseDriverVersion(),
            '_version' => \Modules\Core\Application::VERSION,
        ], $extra));
    }

    /**
     * Capture the cron job environment.
     */
    public static function captureCron(): void
    {
        settings()->set([
            '_last_cron_run' => now(),
            '_cron_job_last_user' => get_current_process_user(),
            '_cron_php_version' => PHP_VERSION,
        ])->save();
    }

    /**
     * Determine whether critical environment values are changed.
     */
    public static function hasChanged(): bool
    {

        if (rtrim(config('app.url'), '/') != rtrim(settings('_app_url'), '/')) {
            return true;
        }

        if (settings('_php_version') != PHP_VERSION) {
            return true;
        }

        $hostname = settings('_server_hostname');

        if (! empty($hostname) && $hostname != gethostname()) {
            return true;
        }

        return false;
    }
}
