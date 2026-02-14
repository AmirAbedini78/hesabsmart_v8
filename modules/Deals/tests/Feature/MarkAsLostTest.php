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
use Modules\Deals\Enums\DealStatus;
use Modules\Users\Models\User;

class MarkAsLostTest extends ResourceTestCase
{
    protected $action = 'mark-as-lost';

    protected $resourceName = 'deals';

    public function test_super_admin_user_can_run_deal_mark_as_lost_action(): void
    {
        $this->signIn();

        $deal = $this->factory()->create();

        $this->runAction($this->action, $deal)->assertActionOk();
        $this->assertSame(DealStatus::lost, $deal->fresh()->status);
    }

    public function test_lost_reason_can_be_provided(): void
    {
        $this->signIn();

        $deal = $this->factory()->create();

        $this->runAction($this->action, $deal, ['lost_reason' => 'Probably cause'])->assertActionOk();
        $this->assertSame(DealStatus::lost, $deal->fresh()->status);
        $this->assertSame('Probably cause', $deal->fresh()->lost_reason);
    }

    public function test_lost_reason_can_be_required_provided(): void
    {
        $this->signIn();

        settings(['lost_reason_is_required' => true]);

        $deal = $this->factory()->create();

        $this->runAction($this->action, $deal, ['lost_reason' => ''])->assertJsonValidationErrorFor('lost_reason');
    }

    public function test_authorized_user_can_run_deal_mark_as_lost_action(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all deals')->signIn();

        $deal = $this->factory()->for(User::factory())->create();

        $this->runAction($this->action, $deal)->assertActionOk();
        $this->assertSame(DealStatus::lost, $deal->fresh()->status);
    }

    public function test_unauthorized_user_can_run_deal_mark_as_lost_action_on_own_deal(): void
    {
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own deals')->signIn();

        $dealForSignedIn = $this->factory()->for($signedInUser)->create();
        $otherDeal = $this->factory()->create();

        $this->runAction($this->action, $otherDeal)->assertActionUnauthorized();
        $this->runAction($this->action, $dealForSignedIn)->assertActionOk();
        $this->assertSame(DealStatus::lost, $dealForSignedIn->fresh()->status);
    }
}
