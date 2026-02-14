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

namespace Modules\ThemeStyle\Providers;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;

class ThemeStyleServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register the service providers.
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
        Innoclapps::style('theme-style', url('theme-style'));
    }

    /**
     * Register the module settins menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::add('system', fn () => SettingsMenuItem::make(
            'theme-style', __('themestyle::style.theme_style'),
        )->path('/theme-style'));
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'ThemeStyle';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'themestyle';
    }
}
