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

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Migrations\Migrator as LaravelMigrator;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Settings\ConfigOverrides;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Installer\RequirementsChecker;
use Modules\Updater\DatabaseMigrator;

class UpdaterServiceProvider extends ModuleServiceProvider
{
    protected bool $withViews = true;

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->when(DatabaseMigrator::class)
            ->needs(LaravelMigrator::class)
            ->give(fn () => $this->app['migrator']);

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register module commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Updater\Console\Commands\UpdateCommand::class,
            \Modules\Updater\Console\Commands\PatchCommand::class,
            \Modules\Updater\Console\Commands\FinalizeUpdateCommand::class,
        ]);
    }

    /**
     * Schedule module tasks.
     */
    protected function scheduleTasks(Schedule $schedule): void
    {
        if ((bool) config('updater.auto_patch')) {
            $schedule->safeCommand('updater:patch')->twiceDaily();
        } else {
            $schedule->safeCommand('updater:patch --critical')->twiceDaily();
        }
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        ConfigOverrides::add([
            'updater.purchase_key' => 'purchase_key',
            'updater.auto_patch' => 'auto_apply_patches',
        ]);
    }

    /**
     * Register the module settins menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::add('system', fn () => SettingsMenuItem::make(
            'update', __('updater::update.update'),
        )->path('/update')->order(1));
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        $requirements = $this->app->get(RequirementsChecker::class);

        return fn () => Auth::check() ? [
            'requirements' => [
                'imap' => $requirements->passes('imap'),
                'zip' => $requirements->passes('zip'),
            ],
        ] : [];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Updater';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'updater';
    }
}
