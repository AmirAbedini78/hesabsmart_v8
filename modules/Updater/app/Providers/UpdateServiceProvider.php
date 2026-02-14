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

namespace Modules\Updater\Providers;

use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\Updater\Patcher;
use Modules\Updater\Updater;

class UpdateServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->singleton(Updater::class, function (Application $app) {
            return new Updater(new Client, new Filesystem, [
                'purchase_key' => $app['config']->get('updater.purchase_key') ?: '',
                'archive_url' => $app['config']->get('updater.archive_url'),
                'download_path' => $app['config']->get('updater.download_path'),
                'version_installed' => $app['config']->get('updater.version_installed'),
                'exclude_folders' => $app['config']->get('updater.exclude_folders'),
                'exclude_files' => $app['config']->get('updater.exclude_files'),
                'permissions' => $app['config']->get('updater.permissions'),
                'show_test_releases' => $app['config']->get('updater.show_test_releases'),
            ]);
        });

        $this->app->singleton(Patcher::class, function (Application $app) {
            return new Patcher(new Client, new Filesystem, [
                'purchase_key' => $app['config']->get('updater.purchase_key') ?: '',
                'patches_url' => $app['config']->get('updater.patches_archive_url'),
                'download_path' => $app['config']->get('updater.download_path'),
                'version_installed' => $app['config']->get('updater.version_installed'),
                'permissions' => $app['config']->get('updater.permissions'),
            ]);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Updater::class, Patcher::class];
    }
}
