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
use Modules\Deals\Models\Stage;
use Modules\Users\Models\User;

class ChangeDealStageTest extends ResourceTestCase
{
    protected $action = 'change-deal-stage';

    protected $resourceName = 'deals';

    public function test_super_admin_user_can_run_change_deal_stage_action(): void
    {
        $this->signIn();
        $pipeline = Pipeline::factory()->withStages()->create();
        $deal = $this->factory()->for($pipeline)->for($pipeline->stages->get(1))->create();
        $stage = $pipeline->stages->get(0);

        $this->runAction($this->action, $deal, ['stage_id' => $stage->id])->assertActionOk();
        $this->assertEquals($stage->id, $deal->fresh()->stage_id);
    }

    public function test_authorized_user_can_run_change_deal_stage_action(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all deals')->signIn();

        $pipeline = Pipeline::factory()->withStages()->create();
        $deal = $this->factory()->for($pipeline)->for($pipeline->stages->get(1))->for(User::factory())->create();
        $stage = $pipeline->stages->get(0);

        $this->runAction($this->action, $deal, ['stage_id' => $stage->id])->assertActionOk();
        $this->assertEquals($stage->id, $deal->fresh()->stage_id);
    }

    public function test_unauthorized_user_can_run_change_deal_stage_action_on_own_deal(): void
    {
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own deals')->signIn();

        $pipeline = Pipeline::factory()->withStages()->create();
        $dealForSignedIn = $this->factory()->for($pipeline)->for($pipeline->stages->get(1))->for($signedInUser)->create();
        $stage = $pipeline->stages->get(0);
        $otherDeal = $this->factory()->create();

        $this->runAction($this->action, $otherDeal, ['stage_id' => $stage->id])->assertActionUnauthorized();
        $this->runAction($this->action, $dealForSignedIn, ['stage_id' => $stage->id])->assertActionOk();
        $this->assertEquals($stage->id, $dealForSignedIn->fresh()->stage_id);
    }

    public function test_change_deal_stage_action_requires_stage(): void
    {
        $this->signIn();

        $deal = $this->factory()->create();

        $this->runAction($this->action, $deal, ['stage_id' => ''])->assertJsonValidationErrors('stage_id');
    }

    public function test_it_updates_the_pipeline_id_if_the_provided_stage_does_not_belongs_to_the_current_deal_pipeline(): void
    {
        $this->signIn();

        $deal = $this->factory()->create();
        $stage = Stage::factory()->create();

        $this->runAction($this->action, $deal, ['stage_id' => $stage->id])->assertActionOk();
        $this->assertEquals($deal->fresh()->pipeline_id, $stage->pipeline_id);
    }
}
