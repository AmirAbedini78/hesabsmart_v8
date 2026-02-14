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

namespace Modules\Updater\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Modules\Updater\DatabaseMigrator;
use Modules\Updater\Updater;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('updater')]
class UpdateControllerTest extends TestCase
{
    use TestsUpdater;

    public function test_unauthenticated_user_cannot_access_update_endpoints(): void
    {
        $this->getJson('api/update')->assertUnauthorized();
        $this->postJson('api/update')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_update_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->getJson('api/update')->assertForbidden();
        $this->postJson('api/update')->assertForbidden();
    }

    public function test_update_information_can_be_retrieved(): void
    {
        $this->signIn();

        App::singleton(Updater::class, function () {
            return $this->createUpdaterInstance([
                new Response(200, [], $this->archiveResponse()),
            ], ['version_installed' => '1.1.0']);
        });

        $this->getJson('/api/update')->assertExactJson([
            'installed_version' => '1.1.0',
            'is_new_version_available' => false,
            'latest_available_version' => '1.1.0',
            'purchase_key' => config('updater.purchase_key'),
        ]);
    }

    public function test_user_can_perform_update(): void
    {
        // Updater runs migration in "handlePostUpdateActions" method, which breaks
        // the transactions in tests, we need to make sure the migration are not executed.
        $this->app->bind(DatabaseMigrator::class, function () {
            return $this->partialMock(DatabaseMigrator::class, function (MockInterface $mock) {
                $mock->shouldReceive('run');
            });
        });

        $this->signIn();

        App::singleton(Updater::class, function () {
            return $this->createUpdaterInstance([
                new Response(200, [], $this->archiveResponse()),
                new Response(200, [], file_get_contents($this->createZipFromFixtureFiles())),
            ]);
        });

        $this->postJson('/api/update')->assertNoContent();
    }

    protected function fixtureFilesPath()
    {
        return module_path('Core', 'tests/Fixtures/update');
    }

    protected function zipPathForFixtureFiles()
    {
        return storage_path('updater/test-1.1.0.zip');
    }
}
