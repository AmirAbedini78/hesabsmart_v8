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

namespace Modules\Installer;

use Exception;

class EnvironmentManager
{
    /**
     * Environment file path.
     */
    protected string $envFilePath;

    /**
     * Initialize new EnvironmentManager instance.
     */
    public function __construct(?string $envFilePath = null)
    {
        $this->envFilePath = $envFilePath ?: app()->environmentFilePath();
    }

    /**
     * Save the form content to the .env file.
     */
    public function saveEnvFile(Environment $env): bool
    {
        $contents = [
            '# Read more about editing the environment file: https://www.concordcrm.com/docs/config#edit-env-file',
            '',
            'APP_NAME' => '\''.$env->getName().'\'',
            '# DO NOT EDIT THE APPLICATION KEY',
            'APP_KEY' => $env->getKey(),
            'IDENTIFICATION_KEY' => $env->getIdentificationKey(),
            'APP_URL' => $env->getUrl(),
            'APP_DEBUG' => 'false',
            '',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $env->getDbHost(),
            'DB_PORT' => $env->getDbPort(),
            'DB_DATABASE' => $env->getDbName(),
            'DB_USERNAME' => $env->getDbUser(),
            'DB_PASSWORD' => '\''.$env->getDbPassword().'\'',
            '',
            'MAIL_MAILER' => 'array',
        ];

        $contents = array_merge($contents, $env->getAdditional());

        try {
            file_put_contents($this->getEnvFilePath(), $this->buildContents($contents));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * Build the .env file contents.
     */
    protected function buildContents(array $contents): string
    {
        $envFile = '';

        foreach ($contents as $var => $value) {
            if (is_int($var)) {
                $envFile .= $value."\n";
            } else {
                $envFile .= $var.'='.$value."\n";
            }
        }

        return $envFile;
    }

    /**
     * Get the environment file path.
     */
    public function getEnvFilePath(): string
    {
        return $this->envFilePath;
    }

    /**
     * Guess the application URL.
     */
    public static function guessUrl(): string
    {
        $guessedUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $guessedUrl .= '://'.$_SERVER['HTTP_HOST'];

        if (! isset($_SERVER['HERD_SITE_PATH']) && ! isset($_SERVER['HERD_HOME'])) {
            $guessedUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }

        $guessedUrl = preg_replace('/install.*/', '', $guessedUrl);

        return rtrim($guessedUrl, '/');
    }
}
