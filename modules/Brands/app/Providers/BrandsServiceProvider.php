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

namespace Modules\Brands\Providers;

use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;

class BrandsServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
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
        DatabaseState::register(\Modules\Brands\Database\State\EnsureDefaultBrandIsPresent::class);
    }

    /**
     * Provide the settings menu items.
     */
    protected function settingsMenu(): SettingsMenuItem
    {
        return SettingsMenuItem::make('brands', __('brands::brand.brands'))
            ->path('/brands')
            ->icon('ColorSwatch')
            ->order(50);
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Brands';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'brands';
    }
}
