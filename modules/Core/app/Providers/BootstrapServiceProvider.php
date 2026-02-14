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

namespace Modules\Core\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Application as CoreApp;
use Modules\Core\Facades\Module;
use Modules\Core\Hooks;
use Modules\Core\Macros\RegistersMacros;
use Modules\Core\Module\FileRepository;
use Modules\Core\Settings\ConfigOverrides;
use Modules\Core\Settings\ConfigRepository;
use Modules\Core\Settings\Contracts\Manager as ManagerContract;
use Modules\Core\Settings\Contracts\Store as StoreContract;
use Modules\Core\Settings\SettingsManager;
use Nwidart\Modules\Contracts\RepositoryInterface;

class BootstrapServiceProvider extends ServiceProvider
{
    use RegistersMacros;

    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    protected array $configs = ['api', 'html_purifier', 'fields', 'integrations', 'settings', 'locales'];

    public function register(): void
    {
        // Tmp for v1.1.7
        if (is_file(config_path('innoclapps.php'))) {
            $this->deleteConflictedLegacyFiles();
            exit(header('Location: /dashboard'));
        }

        $this->app->singleton(Hooks::class, fn () => new Hooks);
        $this->app->alias(Hooks::class, 'hooks');

        $this->app->singleton(CoreApp::class, fn () => new CoreApp);
        $this->app->alias(CoreApp::class, 'core');

        $this->app->bind(RepositoryInterface::class, FileRepository::class);

        $this->registerSettings();
    }

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerMacros();
        $this->configureApiRateLimit();

        ConfigOverrides::add($this->app['config']->get('settings.override', []));
        Model::preventLazyLoading(! app()->isProduction());
        Schema::defaultStringLength(191);
        JsonResource::withoutWrapping();

        $this->increaseCliMemoryLimit();
        $this->forceSsl();
        $this->configureBroadcastConnection();
        $this->registerModulesBroadcastChannels();
    }

    protected function registerSettings(): void
    {
        $this->app->singleton(ManagerContract::class, function (Application $app) {
            $manager = new SettingsManager($app);

            foreach ($app['config']->get('settings.drivers', []) as $driver => $params) {
                $manager->registerStore($driver, $params);
            }

            return $manager;
        });

        $this->app->extend('config', function (Repository $repository) {
            return new ConfigRepository($repository->all());
        });

        $this->app->singleton(StoreContract::class, function (Application $app) {
            return $app[ManagerContract::class]->driver();
        });
    }

    protected function configureBroadcastConnection(): void
    {
        $config = $this->app['config'];

        $keyOptions = Arr::only(
            $config->get('broadcasting.connections.pusher'),
            ['key', 'secret', 'app_id']
        );

        $pusherEnabled = count(array_filter($keyOptions)) === count($keyOptions);

        $pusherOptions = $config->get('broadcasting.connections.pusher.options');

        $config->set('broadcasting.default', $pusherEnabled ? 'pusher' : 'null');

        if ($pusherEnabled && ! str_starts_with($pusherOptions['host'], 'api-'.$pusherOptions['cluster'])) {
            $config->set(
                'broadcasting.connections.pusher.options.host',
                'api-'.$pusherOptions['cluster'].'.pusher.com'
            );
        }
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );

        foreach ($this->configs as $config) {
            $this->mergeConfigFrom(
                module_path($this->moduleName, "config/$config.php"),
                $config
            );
        }
    }

    protected function forceSsl(): void
    {
        if (str_starts_with($this->app['config']->get('app.url'), 'https://')) {
            $this->app['config']->set('core.force_ssl', true);
        }

        if ($this->app['config']->get('core.force_ssl')) {
            URL::forceScheme('https');
        }
    }

    protected function increaseCliMemoryLimit(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $memoryLimit = $this->app['config']->get('core.cli_memory_limit');

        if (! empty($memoryLimit)) {
            \DetachedHelper::raiseMemoryLimit($memoryLimit);
        }
    }

    protected function configureApiRateLimit(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                $this->app['config']->get('api.rate_limit', 90)
            )->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function registerModulesBroadcastChannels(): void
    {
        foreach (Module::allEnabled() as $module) {
            $module->registerBroadcastChannels();
        }
    }

    protected function deleteConflictedLegacyFiles(): void
    {
        File::delete(config_path('innoclapps.php'));
        File::delete(app_path('Console/Commands/FinalizeUpdateCommand.php'));
        File::delete(app_path('Console/Commands/GenerateJsonLanguageFileCommand.php'));
        File::delete(app_path('Console/Commands/SendScheduledDocuments.php'));
        File::delete(app_path('Console/Commands/ActivitiesNotificationsCommand.php'));
        File::delete(app_path('Console/Commands/UpdateCommand.php'));
        File::delete(config_path('updater.php'));
        File::delete(config_path('settings.php'));
        File::delete(config_path('fields.php'));

        if (is_file(config_path('purifier.php'))) {
            File::delete(config_path('purifier.php'));
        }

        File::delete(config_path('html_purifier.php'));
    }
}
