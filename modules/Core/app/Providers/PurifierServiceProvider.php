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

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\HtmlPurifier\Purifier;

class PurifierServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->singleton('purifier', function (Container $app) {
            return new Purifier($app['files'], $app['config']);
        });

        $this->app->alias('purifier', Purifier::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['purifier'];
    }
}
