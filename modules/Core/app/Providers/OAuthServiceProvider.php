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

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Common\OAuth\State\StateStorageManager;
use Modules\Core\Contracts\OAuth\StateStorage;

class OAuthServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StateStorage::class, function (Application $app) {
            return new StateStorageManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [StateStorage::class];
    }
}
