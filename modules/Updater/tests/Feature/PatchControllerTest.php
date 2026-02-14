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
use Modules\Updater\Patcher;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('updater')]
class PatchControllerTest extends TestCase
{
    use TestsUpdater;

    public function test_unauthenticated_user_cannot_access_patches_endpoints(): void
    {
        $this->getJson('api/patches')->assertUnauthorized();
        $this->postJson('api/patches')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_patches_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->getJson('api/patches')->assertForbidden();
        $this->postJson('api/patches')->assertForbidden();
    }

    public function test_user_can_retrieve_available_patches(): void
    {
        $this->signIn();

        App::singleton(Patcher::class, function () {
            return $this->createPatcherInstance([
                new Response(200, [], $this->patcherResponse()),
            ]);
        });

        $this->getJson('/api/patches')->assertJsonCount(2);
    }

    public function test_user_cannot_apply_patch_that_is_already_applied(): void
    {
        $this->signIn();

        $response = json_encode([
            [
                'date' => '2021-08-24T18:52:54.000000Z',
                'description' => 'Fixes issue with activities',
                'token' => '96671235-ddb3-40ab-8ab9-3ca5df8de6b7',
                'version' => '1.0.0',
            ],
        ]);

        App::singleton(Patcher::class, function () use ($response) {
            return $this->createPatcherInstance([
                new Response(200, [], $response),
                new Response(200, [], $response),
            ]);
        });

        $patcher = app(Patcher::class);
        $patch = $patcher->find('96671235-ddb3-40ab-8ab9-3ca5df8de6b7');
        $patch->markAsApplied();

        $this->postJson("/api/patches/{$patch->token()}")->assertStatusConflict();
    }
}
