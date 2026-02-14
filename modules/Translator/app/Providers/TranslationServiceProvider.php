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

use Illuminate\Foundation\Application;
use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;
use Modules\Translator\Contracts\TranslationLoader;
use Modules\Translator\LoaderManager;
use Modules\Translator\Loaders\OverrideFileLoader;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    /**
     * Register any module services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(TranslationLoader::class, function (Application $app) {
            return new OverrideFileLoader(
                $app['config']->get('translator.custom', lang_path('.custom'))
            );
        });
    }

    /**
     * Register the translation line loader. This method registers a
     * `LoaderManager` instead of a simple `FileLoader` as the
     * applications `translation.loader` instance.
     */
    protected function registerLoader(): void
    {
        $this->app->singleton('translation.loader', function (Application $app) {
            return new LoaderManager($app['files'], $app['path.lang']);
        });
    }
}
