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

use Modules\Deals\Models\Pipeline;
use Tests\TestCase;

class PipelineStageControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_pipeline_stages_endpoints(): void
    {
        $pipeline = Pipeline::factory()->create();

        $this->getJson('/api/pipelines/'.$pipeline->id.'/stages')->assertUnauthorized();
    }

    public function test_user_can_retrieve_pipeline_stages(): void
    {
        $this->signIn();

        $pipeline = Pipeline::factory()->withStages([['name' => 'Stage Name', 'win_probability' => 20]])->create();

        $this->getJson("/api/pipelines/{$pipeline->id}/stages")
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Stage Name');
    }
}
