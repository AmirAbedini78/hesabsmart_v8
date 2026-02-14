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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JsonSerializable;
use Maatwebsite\Excel\Concerns\FromArray;

class SystemInfo implements Arrayable, FromArray, JsonSerializable
{
    protected static array $extra = [];

    /**
     * Initialize new SystemInfo class
     */
    public function __construct(protected Request $request) {}

    /**
     * Register system info.
     */
    public static function register(string $key, $value): void
    {
        static::$extra[$key] = $value;
    }

    /**
     * Trasnform the system info to array.
     *
     * @return array
     */
    public function toArray()
    {
        $allowUrlFOpen = ini_get('allow_url_fopen') == '1' || strtolower(ini_get('allow_url_fopen')) == 'on';

        $lastCronRunAt = ! empty(settings('_last_cron_run')) ?
            Carbon::parse(settings('_last_cron_run'))->diffForHumans() :
                'N/A';

        $SQLMode = ! app()->runningUnitTests() ?
            DB::query()->selectRaw('@@sql_mode as mode')->get()[0]->mode :
            'N/A';

        return array_merge([
            'OS' => PHP_OS,
            'Webserver' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'Server Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
            'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',

            'PHP Version' => PHP_VERSION,

            'Last Cron Run' => $lastCronRunAt,
            'Cron Job User' => settings('_cron_job_last_user') ?: 'N/A',
            'Cron PHP Version' => settings('_cron_php_version') ?: 'N/A',

            'PHP IMAP Extension' => extension_loaded('imap'),
            'PHP ZIP Extension' => extension_loaded('zip'),
            'PHP proc_open function' => function_exists('proc_open'),
            'PHP proc_close function' => function_exists('proc_close'),
            'register_argc_argv' => ini_get('register_argc_argv') ?: 'N/A',
            'max_input_vars' => ini_get('max_input_vars') ?: 'N/A',
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: 'N/A',
            'post_max_size' => ini_get('post_max_size') ?: 'N/A',
            'max_execution_time' => ini_get('max_execution_time') ?: 'N/A',
            'memory_limit' => ini_get('memory_limit') ?: 'N/A',

            'allow_url_fopen' => $allowUrlFOpen ? 'Enabled' : 'Disabled',

            'PHP Executable' => \Modules\Core\Application::getPhpExecutablePath() ?: 'N/A',
            'Installed Version' => \Modules\Core\Application::VERSION,
            'CloudFlare' => $this->request->headers->has('Cf-Ray') ? 'Yes' : 'No',

            'Installation Path' => base_path(),
            'Installation Date' => Environment::getInstallationDate(),
            'Last Updated Date' => Environment::getUpdateDate() ?: 'N/A',

            'Current Process User' => get_current_process_user(),

            'DB Driver' => Environment::getDatabaseDriver(),
            'DB Driver Version' => Environment::getDatabaseDriverVersion(),
            'SQL Mode' => $SQLMode,

            'DB_CONNECTION' => config('database.default'),
            'APP_ENV' => config('app.env'),
            'APP_URL' => config('app.url'),
            'APP_DEBUG' => config('app.debug'),
            'SANCTUM_STATEFUL_DOMAINS' => config('sanctum.stateful'),
            'MAIL_MAILER' => config('mail.default'),
            'CACHE_DRIVER' => config('cache.default'),
            'SESSION_DOMAIN' => config('session.domain'),
            'SESSION_DRIVER' => config('session.driver'),
            'SESSION_LIFETIME' => config('session.lifetime'),
            'QUEUE_CONNECTION' => config('queue.default'),
            'LOG_CHANNEL' => config('logging.default'),
            'SETTINGS_DRIVER' => config('settings.default'),
            'MEDIA_DISK' => config('mediable.default_disk'),
            'FILESYSTEM_DISK' => config('filesystems.default'),
            'BROADCAST_CONNECTION' => config('broadcasting.default'),
            'ENABLE_FAVICON' => config('core.favicon_enabled'),
            'HTML_PURIFY' => config('html_purifier.enabled'),
            'SYNC_INTERVAL' => config('core.synchronization.interval'),
            'PRUNE_TRASHED_RECORDS_AFTER' => config('core.soft_deletes.prune_after'),
            'MAX_IMPORT_ROWS' => config('core.import.max_rows'),
            'IMPORT_REVERTABLE_HOURS' => config('core.import.revertable_hours'),
            'PREFERRED_DEFAULT_REMINDER_MINUTES' => config('core.defaults.reminder_minutes'),
        ], static::$extra);
    }

    /**
     * Array function for the export
     */
    public function array(): array
    {
        return [collect($this->toArray())->map(function ($value, $variableName) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            return [$variableName, $value];
        })];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
