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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Environment;
use Modules\Core\Facades\Innoclapps;
use Modules\Updater\Events\UpdateSucceeded;
use Modules\Updater\Exceptions\CannotOpenZipArchiveException;
use Modules\Updater\Exceptions\HasWrongPermissionsException;
use Modules\Updater\Exceptions\InvalidPurchaseKeyException;
use Modules\Updater\Exceptions\MinPHPVersionRequirementException;
use Modules\Updater\Exceptions\PurchaseKeyEmptyException;
use Modules\Updater\Exceptions\PurchaseKeyUsedException;
use Modules\Updater\Exceptions\ReleaseDoesNotExistsException;
use Modules\Updater\Exceptions\UpdaterException;

final class Updater
{
    use ChecksPermissions, ExchangesPurchaseKey;

    /**
     * @var int
     */
    const MIN_PHP_VERSION_REQUIREMENT_CODE = 495;

    /**
     * @var int
     */
    const RELEASE_DOES_NOT_EXISTS_CODE = 496;

    /**
     * @var int
     */
    const PURCHASE_KEY_USED_CODE = 499;

    /**
     * @var int
     */
    const INVALID_PURCHASE_KEY_CODE = 498;

    /**
     * @var int
     */
    const PURCHASE_KEY_EMPTY_CODE = 497;

    /**
     * Available releases cache.
     */
    protected Collection $releases;

    /**
     * The path where the update files will be extracted.
     */
    protected string $basePath;

    /**
     * Indicates whether to show test releases.
     */
    protected static bool $showTestReleases = false;

    /**
     * Initialize new Updater instance.
     */
    public function __construct(protected Client $client, protected Filesystem $filesystem, protected array $config)
    {
        $this->basePath = base_path();
        $this->releases = new Collection;

        self::$showTestReleases = $config['show_test_releases'] ?? false;

        $this->filesystem->ensureDirectoryExists($this->getDownloadPath());
    }

    /**
     * Fetch the latest version, optionally provide a version to fetch.
     *
     * @throws \Modules\Updater\Exceptions\UpdaterException
     */
    public function fetch(string $version = ''): Release
    {
        $release = $this->find($version);

        if (! $release->archive()->exists()) {
            $this->download($release);
        }

        return $release;
    }

    /**
     * Find a release by given version.
     *
     * @throws \Modules\Updater\Exceptions\UpdaterException
     */
    public function find(string $version): Release
    {
        $releases = $this->getAvailableReleases();

        if ($releases->isEmpty()) {
            throw new UpdaterException("The {$version} version could not be found.", 404);
        }

        // If version is not provided, will use the latest
        $release = $releases->first();

        if (! empty($version) &&
         $found = $releases->first(fn ($release) => $release->getVersion() === $version)) {
            /** @var \Modules\Updater\Release */
            $release = $found;
        }

        return $release;
    }

    /**
     * Perform the given release update process.
     *
     * @throws \Modules\Updater\Exceptions\HasWrongPermissionsException
     */
    public function update(Release|string $release, bool $deleteSource = true): bool
    {
        if (! $this->checkPermissions($this->basePath, $this->config['permissions']['exclude_folders'])) {
            throw new HasWrongPermissionsException;
        }

        if (is_string($release)) {
            $release = $this->fetch($release);
        }

        if (! $release->archive()->exists()) {
            $this->download($release);
        }

        $cleaner = new StaleAssetsCleaner($this->filesystem);

        try {
            $cleaner->capture();

            $this->handlePreUpdateActions();

            $release->archive()
                ->excludedDirectories($this->config['exclude_folders'])
                ->excludedFiles($this->config['exclude_files'])
                ->after($cleaner->clean(...))
                ->extract($this->basePath, $deleteSource);
        } catch (CannotOpenZipArchiveException $e) {
            // Delete the source in case of invalid .zip archive
            $release->archive()->deleteSource();

            throw $e;
        }

        $this->handlePostUpdateActions();

        UpdateSucceeded::dispatch($release);

        return true;
    }

    /**
     * Get the available releases.
     *
     * @throws \Modules\Updater\Exceptions\UpdaterException
     */
    public function getAvailableReleases(): Collection
    {
        if ($this->releases->isNotEmpty()) {
            return $this->releases;
        }

        if (empty($url = $this->config['archive_url'])) {
            throw new UpdaterException('Archive URL not specified, please enter a valid URL in your config.', 500);
        }

        try {
            $count = preg_match_all(
                "/\d+\.\d+\.\d+.zip/i",
                $this->client->get(
                    self::createInternalRequestUrl($url)
                )->getBody()->getContents(),
                $files
            );
        } catch (RequestException $e) {
            throw new UpdaterException($e->getMessage(), $e->getCode());
        }

        $url = preg_replace('/\/$/', '', $url);

        for ($i = 0; $i < $count; $i++) {
            $version = basename(preg_replace('/.zip$/', '', $files[0][$i]));
            $zipBallUrl = $url.'/'.'v'.$files[0][$i];

            $release = (new Release($version, $this->filesystem))
                ->setStoragePath(
                    Str::finish($this->config['download_path'], DIRECTORY_SEPARATOR).$version.'.zip'
                )
                ->setAccessToken($this->getPurchaseKey())
                ->setDownloadUrl(self::createInternalRequestUrl($zipBallUrl));

            $this->releases->push($release);
        }

        // Sort releases alphabetically descending to have newest package as first
        return $this->releases = $this->releases->sortByDesc(
            fn ($release) => $release->getVersion()
        )->values();
    }

    /**
     * Get the latest available version.
     * Example: 2.6.5
     *
     * @throws \Exception
     */
    public function getVersionAvailable(): string
    {
        $releaseCollection = $this->getAvailableReleases();

        if ($releaseCollection->isEmpty()) {
            return '';
        }

        return $releaseCollection->first()->getVersion();
    }

    /**
     * Get the version that is currenly installed.
     */
    public function getVersionInstalled(): string
    {
        return $this->config['version_installed'];
    }

    /**
     * Check whether a new version is available.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function isNewVersionAvailable(string $currentVersion = ''): bool
    {
        $version = $currentVersion ?: $this->getVersionInstalled();

        if (! $version) {
            throw new InvalidArgumentException('No currently installed version specified.');
        }

        return version_compare($version, $this->getVersionAvailable(), '<');
    }

    /**
     * Get the updater download path.
     */
    public function getDownloadPath(string $glue = ''): string
    {
        return $this->config['download_path'].$glue;
    }

    /**
     * Create an internal request URL with necessary information.
     */
    public static function createInternalRequestUrl(string $endpoint, array $extra = []): string
    {
        return $endpoint.'?'.http_build_query(array_merge([
            'identification_key' => config('core.key'),
            'app_url' => config('app.url'),
            'installed_version' => \Modules\Core\Application::VERSION,
            'server_ip' => Environment::getServerIp(),
            'installed_date' => Environment::getInstallationDate(),
            'last_updated_date' => Environment::getUpdateDate(),
            'php_version' => PHP_VERSION,
            'locale' => app()->getLocale(),
            'test' => self::$showTestReleases,
            'database_driver' => Environment::getDatabaseDriver(),
            'database_driver_version' => Environment::getDatabaseDriverVersion(),
        ], $extra));
    }

    /**
     * Clear the temporary download path.
     */
    public function clearTemporaryPath(): void
    {
        $this->filesystem->deepCleanDirectory($this->config['download_path']);
    }

    /**
     * Increase php.ini values if possible
     */
    public function increasePhpIniValues(): void
    {
        \DetachedHelper::raiseMemoryLimit('256M');

        if (function_exists('set_time_limit')) {
            set_time_limit(240);
        } else {
            @ini_set('max_execution_time', 240);
        }
    }

    /**
     * Handle the pre update actions.
     */
    protected function handlePreUpdateActions(): void
    {
        // Always clear the cache pre update to ensure nothing is cached when reloading.
        Innoclapps::clearCache();
    }

    /**
     * Handle the post update actions.
     */
    protected function handlePostUpdateActions(): void
    {
        $this->clearTemporaryPath();
    }

    /**
     * Download the release .zip file.
     */
    protected function download(Release $release): void
    {
        try {
            $release->download($this->client);
        } catch (ClientException $e) {
            throw match ($e->getCode()) {
                self::MIN_PHP_VERSION_REQUIREMENT_CODE => new MinPHPVersionRequirementException,
                self::RELEASE_DOES_NOT_EXISTS_CODE => new ReleaseDoesNotExistsException,
                self::PURCHASE_KEY_USED_CODE => new PurchaseKeyUsedException,
                self::INVALID_PURCHASE_KEY_CODE => new InvalidPurchaseKeyException,
                self::PURCHASE_KEY_EMPTY_CODE => new PurchaseKeyEmptyException,
                default => $e,
            };
        }
    }
}
