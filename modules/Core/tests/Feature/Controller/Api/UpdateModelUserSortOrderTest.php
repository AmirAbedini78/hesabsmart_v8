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

use Modules\Deals\Models\Pipeline;
use Tests\TestCase;

class UpdateModelUserSortOrderTest extends TestCase
{
    public function test_it_can_update_model_user_sort_order(): void
    {
        $user = $this->signIn();

        $pipelines = Pipeline::factory(3)->create();

        $this->patchJson('/api/models/pipeline/sort-order', [
            'module' => 'deals',
            'order' => [
                ['id' => $pipelines[2]->id, 'display_order' => 1],
                ['id' => $pipelines[1]->id, 'display_order' => 2],
                ['id' => $pipelines[0]->id, 'display_order' => 3],
            ],
        ])->assertNoContent();

        $freshPipelines = Pipeline::orderByUserSpecified($user)->with('currentUserSortedModel')->get();

        $this->assertSame($pipelines[2]->id, $freshPipelines[0]->id);
        $this->assertSame($pipelines[1]->id, $freshPipelines[1]->id);
        $this->assertSame($pipelines[0]->id, $freshPipelines[2]->id);

        $this->assertSame(1, $freshPipelines[0]->currentUserSortedModel->display_order);
        $this->assertSame(2, $freshPipelines[1]->currentUserSortedModel->display_order);
        $this->assertSame(3, $freshPipelines[2]->currentUserSortedModel->display_order);
    }
}
