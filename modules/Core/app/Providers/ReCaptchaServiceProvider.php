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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Support\ReCaptcha;

class ReCaptchaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('recaptcha', function (Application $app) {
            return (new ReCaptcha(Request::instance()))
                ->setSiteKey($app['config']->get('core.recaptcha.site_key'))
                ->setSecretKey($app['config']->get('core.recaptcha.secret_key'))
                ->setSkippedIps($app['config']->get('core.recaptcha.ignored_ips', []));
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['recaptcha'];
    }
}
