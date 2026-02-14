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

use Modules\Core\Models\DataView;
use Tests\TestCase;

class DataViewControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_views_endpoints(): void
    {
        $view = DataView::factory()->create();

        $this->getJson('/api/views')->assertUnauthorized();
        $this->postJson('/api/views')->assertUnauthorized();
        $this->putJson('/api/views/'.$view->id)->assertUnauthorized();
        $this->deleteJson('/api/views/'.$view->id)->assertUnauthorized();
    }

    public function test_user_can_retrieve_views(): void
    {
        $user = $this->signIn();

        DataView::factory(5)->for($user)->create();

        $this->getJson('/api/views/users')->assertJsonCount(5);
    }

    public function test_user_can_retrieve_authorized_views(): void
    {
        $user = $this->signIn();
        $otherUser = $this->createUser();

        DataView::factory()->shared()->create();
        DataView::factory(5)->for($user)->create();
        DataView::factory()->for($otherUser)->create();

        $this->getJson('/api/views/users')->assertJsonCount(6);
    }

    public function test_user_can_create_view(): void
    {
        $this->signIn();

        $this->postJson('/api/views', $data = [
            'identifier' => 'users',
            'name' => 'View Name',
            'is_shared' => true,
            'rules' => $this->filterRulesPayload(),
        ])->assertJson($data);
    }

    public function test_authorized_user_can_update_view(): void
    {
        $user = $this->signIn();

        $view = DataView::factory()->for($user)->create();

        $this->putJson('/api/views/'.$view->id, $data = [
            'name' => 'New View Name',
            'is_shared' => true,
            'rules' => $this->filterRulesPayload(),
        ])->assertJson($data);
    }

    public function test_unauthorized_user_can_update_view(): void
    {
        $user = $this->signIn();

        $view = DataView::factory()->for($user)->create(
            ['name' => $name = 'View Name']
        );

        $this->asRegularUser()->signIn();

        $this->putJson('/api/views/'.$view->id, [
            'name' => 'New View Name',
            'is_shared' => true,
        ])->assertForbidden();

        $this->assertDatabaseHas('data_views', [
            'name' => $name,
            'is_shared' => false,
        ]);
    }

    public function test_authorized_user_can_delete_view(): void
    {
        $user = $this->signIn();

        $view = DataView::factory()->for($user)->create();

        $this->deleteJson('/api/views/'.$view->id);
        $this->assertModelMissing($view);
    }

    public function test_unauthorized_user_cannot_delete_view(): void
    {
        $user = $this->signIn();

        $view = DataView::factory()->for($user)->create();

        // Sign new user
        $this->asRegularUser()->signIn();

        $this->deleteJson('/api/views/'.$view->id)
            ->assertForbidden();

        $this->assertDatabaseHas('data_views', [
            'id' => $view->id,
        ]);
    }

    public function test_user_can_share_view(): void
    {
        $this->signIn();

        $payload = DataView::factory()->shared()->make()->toArray();
        $response = $this->postJson('/api/views', $payload);
        $viewId = $response->getData()->id;

        $newUser = $this->signIn();
        $response = $this->getJson('/api/users/table/settings')->assertOk();

        $data = $response->getData();

        $this->assertTrue($viewId === $data->views[0]->id);
        $this->assertFalse($newUser->id === $data->views[0]->user_id);
    }

    public function test_it_updates_only_config_on_system_default_views(): void
    {
        $this->signIn();
        $otherUser = $this->createUser();

        $view = DataView::factory()->shared()->default()->create([
            'name' => 'View Name',
            'identifier' => 'users',
            'rules' => $originalRules = $this->filterRulesPayload(),
        ]);

        $this->putJson('/api/views/'.$view->id, [
            'config' => ['something'],

            'name' => 'Changed Name',
            'is_shared' => false,
            'identifier' => 'changed-identifier',
            'user_id' => $otherUser->id,
            'rules' => $this->filterRulesPayload('Other Value'),
        ])->assertJson([
            'config' => ['something'],

            'is_shared' => true,
            'name' => 'View Name',
            'user_id' => null,
            'identifier' => $view->identifier,
            'rules' => $originalRules,
        ]);
    }

    public function test_system_default_view_cannot_be_deleted(): void
    {
        $this->signIn();

        $view = DataView::factory()->default()->create();

        $this->deleteJson('/api/views/'.$view->id)->assertForbidden();
    }

    protected function filterRulesPayload(string $value = 'Dummy Value'): array
    {
        return [
            [
                'condition' => 'all',
                'children' => [[
                    'type' => 'rule',
                    'query' => [
                        'type' => 'text',
                        'operator' => 'equal',
                        'rule' => 'attribute_name',
                        'operand' => null,
                        'value' => $value,
                    ],
                ]],
            ],
        ];
    }
}
