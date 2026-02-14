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

namespace Modules\Core\Support;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\SettingsMenu;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected array $resources = [];

    protected array $notifications = [];

    protected array $mailableTemplates = [];

    protected bool $withViews = false;

    protected bool $withTranslations = true;

    protected bool $withConfig = true;

    protected bool $withMigrations = true;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->booting($this->baseBoot(...));
    }

    /**
     * Provide the module name.
     */
    abstract protected function moduleName(): string;

    /**
     * Provide the module name in lowercase.
     */
    abstract protected function moduleNameLower(): string;

    /**
     * Prepare module for booting.
     */
    protected function baseBoot(): void
    {
        if ($this->withConfig) {
            $this->registerConfig();
        }

        if ($this->withTranslations) {
            $this->registerTranslations();
        }

        if ($this->withViews) {
            $this->registerViews();
        }

        if ($this->withMigrations) {
            $this->registerMigrations();
        }

        $this->registerMailableTemplates();
        $this->registerNotifications();

        if (method_exists($this, 'registerMenuItems')) {
            $this->registerMenuItems();
        }

        if (method_exists($this, 'registerSettingsMenuItems')) {
            $this->registerSettingsMenuItems();
        }

        if (method_exists($this, 'menu')) {
            Menu::register(function () {
                return $this->menu();
            });
        }

        if (method_exists($this, 'settingsMenu')) {
            SettingsMenu::register(function () {
                return $this->settingsMenu();
            });
        }

        $this->registerResources();

        $this->app->booted(function () {
            /** @var \Illuminate\Console\Scheduling\Schedule */
            $schedule = $this->app->make(Schedule::class);
            $this->scheduleTasks($schedule);
        });

        $this->shareScriptData();

        $this->app->booted($this->setup(...));
    }

    /**
     * Register module migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName(), 'database/migrations'));
    }

    /**
     * Register module config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName(), 'config/config.php'),
            $this->moduleNameLower()
        );
    }

    /**
     * Register module views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(
            module_path($this->moduleName(), 'resources/views'),
            $this->moduleNameLower()
        );

        Blade::componentNamespace($this->getComponentNamespaces(), $this->moduleNameLower());
    }

    /**
     * Register module translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(
            module_path($this->moduleName(), 'lang'),
            $this->moduleNameLower()
        );
    }

    /**
     * Schedule module tasks.
     */
    protected function scheduleTasks(Schedule $schedule): void
    {
        //
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        //
    }

    /**
     * Register module resources.
     */
    protected function registerResources(): void
    {
        Innoclapps::resources($this->resources);
    }

    /**
     * Register module notifications.
     */
    protected function registerNotifications(): void
    {
        Innoclapps::notifications($this->notifications);
    }

    /**
     * Register module mailable templates.
     */
    protected function registerMailableTemplates(): void
    {
        Innoclapps::mailableTemplates($this->mailableTemplates);
    }

    /**
     * Share data to script.
     */
    protected function shareScriptData(): void
    {
        if (method_exists($this, 'scriptData')) {
            Innoclapps::whenReadyForServing(function () {
                Innoclapps::provideToScript($this->scriptData());
            });
        }
    }

    /**
     * Get the module components namespaces.
     */
    protected function getComponentNamespaces()
    {
        return str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName().'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder', '')));
    }
}
