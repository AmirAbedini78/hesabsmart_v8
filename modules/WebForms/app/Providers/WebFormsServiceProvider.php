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

namespace Modules\WebForms\Providers;

use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Users\Events\TransferringUserData;
use Modules\WebForms\Listeners\TransferWebFormUserData;

class WebFormsServiceProvider extends ModuleServiceProvider
{
    protected bool $withViews = true;

    protected array $mailableTemplates = [
        \Modules\WebForms\Mail\WebFormSubmitted::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(TransferringUserData::class, TransferWebFormUserData::class);
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Provide the settins menu items.
     */
    protected function settingsMenu(): SettingsMenuItem
    {
        return SettingsMenuItem::make('web-forms', __('webforms::form.forms'))
            ->path('/forms')
            ->icon('MenuAlt3')
            ->order(30);
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'WebForms';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'webforms';
    }
}
