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

namespace Modules\Translator\Providers;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\Tool;
use Modules\Translator\Translator;
use Modules\Updater\Events\UpdateFinalized;

class TranslatorServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->registerCommands();

        $this->app['events']->listen(UpdateFinalized::class, Translator::generateJsonLanguageFile(...));
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        Innoclapps::tools(function () {
            return Tool::new('json-language', Translator::generateJsonLanguageFile(...))
                ->description(__('translator::translator.tools.json-language'));
        });
    }

    /**
     * Register the module settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::add('system', fn () => SettingsMenuItem::make(
            'translator', __('translator::translator.translator'),
        )->path('/translator'));
    }

    /**
     * Register the module commands.
     */
    public function registerCommands(): void
    {
        $this->commands([
            \Modules\Translator\Console\Commands\GenerateJsonLanguageFile::class,
        ]);
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Translator';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'translator';
    }
}
