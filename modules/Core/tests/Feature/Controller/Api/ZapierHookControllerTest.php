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

namespace Modules\Core\Tests\Feature\Controller\Api;

use Tests\TestCase;

class ZapierHookControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_zapier_hooks_endpoints(): void
    {
        $this->postJson('/api/zapier/hooks/DUMMY_RESOURCE/DUMMY_ACTION')->assertUnauthorized();
        $this->deleteJson('/api/zapier/hooks/DUMMY_ID')->assertUnauthorized();
    }

    public function test_zapier_can_subscribe_to_an_action(): void
    {
        $user = $this->signIn();

        $this->postJson('/api/zapier/hooks/events/create', [
            'targetUrl' => $url = 'https://concordcrm.com',
            'zapId' => 123,
            'data' => ['dummy-data' => 'dummy-value'],
        ])->assertCreated()
            ->assertJson([
                'user_id' => $user->id,
                'hook' => $url,
                'zap_id' => 123,
                'data' => ['dummy-data' => 'dummy-value'],
            ]);
    }

    public function test_zapier_can_unsubscribe_from_an_action(): void
    {
        $this->signIn();

        $id = $this->postJson('/api/zapier/hooks/events/create', [
            'targetUrl' => 'https://concordcrm.com',
            'zapId' => 123,
            'data' => ['dummy-data' => 'dummy-value'],
        ])->getData()->id;

        $this->deleteJson('/api/zapier/hooks/'.$id)->assertNoContent();

        $this->assertDatabaseMissing('zapier_hooks', ['id' => $id]);
    }
}
