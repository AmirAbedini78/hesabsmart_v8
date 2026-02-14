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

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PreInstall
{
    private string $envPath;

    private string $envExamplePath;

    private string $configCachePath;

    /**
     * Initialize new PreInstall instance.
     */
    public function __construct(private string $rootDir, private string $url)
    {
        $this->url = $url;
        $this->rootDir = rtrim($rootDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->envPath = $this->rootDir.'.env';
        $this->envExamplePath = $this->rootDir.'.env.example';
        $this->configCachePath = $this->rootDir.'bootstrap/cache/config.php';
    }

    /**
     * Init the pre-installation.
     */
    public function init(): void
    {
        $this->createEnvFileIfNotExists();

        $this->ensureKeyIsSetInEnvironmentFile();

        $this->ensureIdentificationKeyIsSetInEnvironmentFile();

        if (! $this->envConfigValueExist('APP_URL')) {
            $this->writeInEnvFile('APP_URL', $this->url);
        }

        if (file_exists($this->configCachePath)) {
            unlink($this->configCachePath);
        }
    }

    /**
     * Check whether the .env file exist.
     */
    private function envFileExist(): bool
    {
        return file_exists($this->envPath);
    }

    /**
     * Check whether the .env value exist.
     */
    private function envConfigValueExist(string $key): bool
    {
        $value = $this->getEnvConfigValue($key);

        return ! (empty($value) || is_null($value));
    }

    /**
     * Check whether the .env.example file exist.
     */
    private function envExampleFileExist(): bool
    {
        return file_exists($this->envExamplePath);
    }

    /**
     * Generate random application key.
     */
    private function generateAppRandomKey(): string
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey('AES-256-CBC') // from config "app.cipher"
        );
    }

    /**
     * Write environment value in the .env file.
     */
    private function writeInEnvFile(string $key, string $value): void
    {
        // If not exists, add the key e.q. "APP_KEY=" to the .env file.
        // This will make sure that the regex replacement below works.
        if (! preg_match('/^'.$key.'=/m', file_get_contents($this->envPath))) {
            if (! file_put_contents($this->envPath, PHP_EOL.$key.'=', FILE_APPEND)) {
                $this->showPermissionsError();
            }
        }

        file_put_contents($this->envPath, preg_replace(
            '/^'.preg_quote($key, '/').'=.*/m',
            $key.'='.$value,
            file_get_contents($this->envPath)
        ));
    }

    /**
     * Get cached config value.
     */
    private function getCachedConfigValue(string $key): mixed
    {
        if (! file_exists($this->configCachePath)) {
            return '';
        }

        $config = include $this->configCachePath;

        if (empty($config)) {
            return '';
        }

        $value = Arr::get($config, $key);

        return ! empty($value) ? $value : '';
    }

    /**
     * Get config value from .env.
     */
    private function getEnvConfigValue(string $key): string
    {
        // Read .env file into $_ENV
        try {
            \Dotenv\Dotenv::create(
                \Illuminate\Support\Env::getRepository(),
                $this->rootDir
            )->load();
        } catch (\Exception) {
            // Do nothing
        }

        return ! empty($_ENV[$key]) ? $_ENV[$key] : '';
    }

    /**
     * Show installer error.
     */
    private function showPreInstallError(string $msg): never
    {
        echo $msg;
        exit;
    }

    /**
     * Create the initial .env file if not exists.
     */
    private function createEnvFileIfNotExists(): void
    {
        // First, let's check if the .env file already exists
        if ($this->envFileExist()) {
            return;
        }

        // Confirm that the .env.sample is in place
        if (! $this->envExampleFileExist()) {
            $this->showPreInstallError(
                'File <strong>.env.example</strong> not found. Please make sure to copy this file from the downloaded files.'
            );
        }

        // Copy .env.example
        copy($this->envExamplePath, $this->envPath);

        if (! $this->envFileExist()) {
            $this->showPermissionsError();
        }
    }

    private function ensureIdentificationKeyIsSetInEnvironmentFile(): void
    {
        if ($this->envConfigValueExist('IDENTIFICATION_KEY')) {
            return;
        }

        $envIdentificationKey = $this->getEnvConfigValue('IDENTIFICATION_KEY');
        $cachedIdentificationKey = $this->getCachedConfigValue('core.key');

        $this->writeInEnvFile(
            'IDENTIFICATION_KEY',
            $cachedIdentificationKey ?: ($envIdentificationKey ?: (string) Str::uuid())
        );
    }

    private function ensureKeyIsSetInEnvironmentFile(): void
    {
        if ($this->envConfigValueExist('APP_KEY')) {
            return;
        }

        $envAppKey = $this->getEnvConfigValue('APP_KEY');
        $cachedAppKey = $this->getCachedConfigValue('app.key');

        $this->writeInEnvFile(
            'APP_KEY',
            $cachedAppKey ?: ($envAppKey ?: $this->generateAppRandomKey())
        );
    }

    /**
     * Helper function to show permissions error
     */
    private function showPermissionsError(): never
    {
        $rootDirNoSlash = rtrim($this->rootDir, DIRECTORY_SEPARATOR);

        $this->showPreInstallError('<div style="font-size:18px;">Web installer could not write data into <strong>'.$this->envPath.'</strong> file. Please give your web server user (<strong>'.get_current_process_user().'</strong>) write permissions in <code><pre style="background: #f0f0f0;
            padding: 15px;
            width: 50%;
            margin-top:0px;
            border-radius: 4px;">
sudo chown '.get_current_process_user().':'.get_current_process_user().' -R '.$rootDirNoSlash.'
sudo find '.$rootDirNoSlash.' -type d -exec chmod 755 {} \;
sudo find '.$rootDirNoSlash.' -type f -exec chmod 644 {} \;
</pre></code>');
    }
}
