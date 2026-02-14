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

namespace Modules\Deals\Tests\Feature;

use Modules\Core\Tests\ResourceTestCase;

class LostReasonResourceTest extends ResourceTestCase
{
    protected $resourceName = 'lost-reasons';

    public function test_user_can_create_resource_record(): void
    {
        $this->signIn();

        $this->postJson($this->createEndpoint(), ['name' => 'Client went silent'])
            ->assertStatus(201)
            ->assertJson(['name' => 'Client went silent']);
    }

    public function test_unauthorized_user_cannot_create_resource_record(): void
    {
        $this->asRegularUser()->signIn();
        $this->postJson($this->createEndpoint(), ['name' => 'Client went silent'])->assertForbidden();
    }

    public function test_user_can_update_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), [
            'name' => 'Changed',
        ])->assertOk()
            ->assertJson(['name' => 'Changed']);
    }

    public function test_unauthorized_user_cannot_update_resource_record(): void
    {
        $this->asRegularUser()->signIn();

        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), [
            'name' => 'Changed',
        ])->assertForbidden();
    }

    public function test_user_can_retrieve_resource_records(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_user_can_retrieve_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_delete_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function test_unauthorized_user_cannot_delete_resource_record(): void
    {
        $this->asRegularUser()->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertForbidden();
    }

    public function test_lost_reason_requires_name(): void
    {
        $this->signIn();

        $this->postJson($this->createEndpoint(), ['name' => ''])->assertJsonValidationErrors(['name']);

        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record))->assertJsonValidationErrors(['name']);
    }

    public function test_lost_reason_name_must_be_unique(): void
    {
        $this->signIn();

        $records = $this->factory()->count(2)->create();

        $this->postJson(
            $this->createEndpoint(),
            ['name' => $records->first()->name,
            ]
        )->assertJsonValidationErrors(['name']);

        $this->putJson(
            $this->updateEndpoint($records->get(1)),
            ['name' => $records->first()->name]
        )->assertJsonValidationErrors(['name']);
    }
}
