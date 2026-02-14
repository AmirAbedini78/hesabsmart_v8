<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Modules\Core\Application;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\CustomFieldFileCache;
use Modules\Core\Fields\FieldsManager;
use Modules\Core\Resource\Resource;
use Modules\Core\Support\GateHelper;
use Modules\Core\Support\ModelFinder;
use Modules\Core\Workflow\Action as WorkflowAction;
use Modules\Core\Workflow\Workflows;
use Modules\Users\Support\TeamCache;
use Tests\Fixtures\CalendarResource;
use Tests\Fixtures\EventResource;

abstract class TestCase extends BaseTestCase
{
    use CreatesUser,
        RefreshDatabase;

    /**
     * Setup the tests.
     */
    protected function setUp(): void
    {
        $_SERVER['_VERSION'] = Application::VERSION;

        $this->configureFactoryResolver();

        parent::setUp();

        $this->withoutMiddleware([
            \Modules\Core\Http\Middleware\BlocksBadVisitors::class,
            \Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished::class,
            \Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded::class,
        ]);

        $this->registerTestResources();
    }

    /**
     * Register the tests resources.
     */
    protected function registerTestResources(): void
    {
        Application::resources([
            EventResource::class,
            CalendarResource::class,
        ]);
    }

    /**
     * Flush any cache and clear registered data.
     */
    protected function flushCacheAndClearData(): void
    {
        $this->tearDownChangelog();

        Application::flushState();
        Workflows::flushState();
        Resource::flushState();
        FieldsManager::flushCache();
        TeamCache::flush();
        CustomFieldFileCache::flush();
        GateHelper::flushCache();
        WorkflowAction::disableExecutions(false);
        Innoclapps::enableNotifications();
    }

    /**
     * Teardown changelog data.
     */
    protected function tearDownChangelog(): void
    {
        foreach ((new ModelFinder)->find() as $model) {
            if (method_exists($model, 'logsModelChanges') && $model::logsModelChanges()) {
                $model::$changesPipes = [];
            }
        }
    }

    /**
     * Configure tests factory resolver.
     */
    protected function configureFactoryResolver(): void
    {
        Factory::guessFactoryNamesUsing(function ($modelName) {
            $appNamespace = 'App\\';
            $testNameSpace = __NAMESPACE__.'\\';
            $laravelFactoriesNamespace = 'Database\\Factories\\';
            $testsFactoriesNamespace = 'Tests\\Factories\\';

            if (Str::startsWith($modelName, $testNameSpace.'Fixtures\\')) {
                return $testsFactoriesNamespace.Str::after($modelName, $testNameSpace.'Fixtures\\').'Factory';
            }

            if (Str::startsWith($modelName, $appNamespace.'Models\\')) {
                $modelName = Str::after($modelName, $appNamespace.'Models\\');
            } else {
                $modelName = Str::after($modelName, $appNamespace);
            }

            return $laravelFactoriesNamespace.$modelName.'Factory';
        });
    }

    /**
     * Tear down the tests.
     */
    protected function tearDown(): void
    {
        $this->flushCacheAndClearData();

        parent::tearDown();
    }
}
