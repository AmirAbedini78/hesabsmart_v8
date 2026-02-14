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
use Modules\Deals\Models\Pipeline;

class PipelineStageResourceTest extends ResourceTestCase
{
    protected $resourceName = 'pipeline-stages';

    public function test_user_can_create_resource_record(): void
    {
        $this->signIn();
        $pipeline = Pipeline::factory()->create();

        $this->postJson($this->createEndpoint(), [
            'name' => 'Test Stage Name',
            'pipeline_id' => $pipeline->id,
            'win_probability' => 100,
        ])->assertCreated()->assertJson([
            'win_probability' => 100,
            'name' => 'Test Stage Name',
            'pipeline_id' => $pipeline->id,
        ]);
    }

    public function test_user_can_update_resource_record(): void
    {
        $this->signIn();
        $stage = $this->factory()->create(['win_probability' => 23]);
        $pipeline = Pipeline::factory()->create();

        $this->putJson($this->updateEndpoint($stage), [
            'name' => 'Test Stage Name',
            'pipeline_id' => $pipeline->id,
            'win_probability' => 100,
        ])->assertOk()->assertJson([
            'win_probability' => 100,
            'name' => 'Test Stage Name',
            'pipeline_id' => $pipeline->id,
        ]);

        // Same name update, unique validation should pass
        $this->putJson($this->updateEndpoint($stage), [
            'name' => $stage->name,
            'pipeline_id' => $pipeline->id,
            'win_probability' => 100,
        ])->assertOk();
    }

    public function test_unauthorized_user_cannot_update_resource_record(): void
    {
        $this->asRegularUser()->signIn();
        $stage = $this->factory()->create();
        $pipeline = Pipeline::factory()->create();

        $this->putJson($this->updateEndpoint($stage), [
            'name' => 'Test Stage Name',
            'pipeline_id' => $pipeline->id,
            'win_probability' => 100,
        ])->assertForbidden();
    }

    public function test_stage_requires_name(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'name' => '',
        ])->assertJsonValidationErrorFor('name');

        $this->putJson($this->updateEndpoint($stage), [
            'name' => '',
        ])->assertJsonValidationErrorFor('name');
    }

    public function test_stage_requires_unique_name(): void
    {
        $this->signIn();
        $pipeline = Pipeline::factory()->create();
        $stage = $this->factory()->for($pipeline)->create();
        $stage2 = $this->factory()->for($pipeline)->create();

        $this->postJson($this->createEndpoint(), [
            'name' => $stage->name,
            'pipeline_id' => $pipeline->id,
        ])->assertJsonValidationErrorFor('name');

        $this->putJson($this->updateEndpoint($stage), [
            'name' => $stage2->name,
            'pipeline_id' => $pipeline->id,
        ])->assertJsonValidationErrorFor('name');
    }

    public function test_stage_requires_pipeline_id(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'pipeline_id' => '',
        ])->assertJsonValidationErrorFor('pipeline_id');

        $this->putJson($this->updateEndpoint($stage), [
            'pipeline_id' => '',
        ])->assertJsonValidationErrorFor('pipeline_id');
    }

    public function test_stage_requires_win_probability(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'win_probability' => '',
        ])->assertJsonValidationErrorFor('win_probability');

        $this->putJson($this->updateEndpoint($stage), [
            'win_probability' => '',
        ])->assertJsonValidationErrorFor('win_probability');
    }

    public function test_stage_win_probability_can_be_max_100(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'win_probability' => 125,
        ])->assertJsonValidationErrorFor('win_probability');

        $this->putJson($this->updateEndpoint($stage), [
            'win_probability' => 125,
        ])->assertJsonValidationErrorFor('win_probability');
    }

    public function test_stage_win_probability_can_be_min_0(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'win_probability' => -2,
        ])->assertJsonValidationErrorFor('win_probability');

        $this->putJson($this->updateEndpoint($stage), [
            'win_probability' => -2,
        ])->assertJsonValidationErrorFor('win_probability');
    }

    public function test_stage_win_probability_must_be_integer(): void
    {
        $this->signIn();
        $stage = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'win_probability' => 'invalid',
        ])->assertJsonValidationErrorFor('win_probability');

        $this->putJson($this->updateEndpoint($stage), [
            'win_probability' => 'invalid',
        ])->assertJsonValidationErrorFor('win_probability');
    }

    public function test_stage_name_is_unique_per_pipeline(): void
    {
        $this->signIn();
        $pipeline = Pipeline::factory()->create();
        $pipeline2 = Pipeline::factory()->create();
        $stage = $this->factory()->for($pipeline)->create();
        $stage2 = $this->factory()->for($pipeline)->create();

        $this->postJson($this->createEndpoint(), [
            'name' => $stage->name,
            'pipeline_id' => $pipeline2->id,
        ])->assertJsonMissingValidationErrors('name');

        $this->putJson($this->updateEndpoint($stage), [
            'name' => $stage2->name,
            'pipeline_id' => $pipeline2->id,
        ])->assertJsonMissingValidationErrors('name');
    }

    public function test_user_can_retrieve_resource_records(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_unauthorized_user_cannot_retrieve_resource_records(): void
    {
        $this->asRegularUser()->signIn();

        $this->getJson($this->indexEndpoint())->assertForbidden();
    }

    public function test_user_can_retrieve_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_retrieve_resource_record(): void
    {
        $this->asRegularUser()->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertForbidden();
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
}
