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

namespace Modules\Notes\Providers;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\Pages\Tab;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Notes\Listeners\TransferNotesUserData;
use Modules\Users\Events\TransferringUserData;

class NotesServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\Notes\Resources\Note::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(TransferringUserData::class, TransferNotesUserData::class);
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
        $this->registerRelatedRecordsDetailTab();
    }

    /**
     * Register the module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('notes', 'notes-tab')->panel('notes-tab-panel')->order(35);

        foreach (['contacts', 'companies', 'deals'] as $resourceName) {
            if ($resource = Innoclapps::resourceByName($resourceName)) {
                $resource->getDetailPage()->tab($tab);
            }
        }
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Notes';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'notes';
    }
}
