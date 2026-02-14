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

namespace Modules\Comments\Providers;

use Modules\Core\Support\ModuleServiceProvider;

class CommentsServiceProvider extends ModuleServiceProvider
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
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Comments';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'comments';
    }
}
