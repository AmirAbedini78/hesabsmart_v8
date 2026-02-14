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

use Modules\Activities\Models\ActivityType;
use Modules\Core\Workflow\Workflows;
use Tests\TestCase;

class WorkflowTriggersControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_workflow_triggers_endpoints(): void
    {
        $this->getJson('/api/workflows/triggers')->assertUnauthorized();
    }

    public function test_workflow_triggers_can_be_retrieved(): void
    {
        ActivityType::factory()->create(['flag' => 'task']);

        $this->signIn();

        $this->getJson('/api/workflows/triggers')
            ->assertJsonCount(Workflows::triggersInstance()->count());
    }
}
