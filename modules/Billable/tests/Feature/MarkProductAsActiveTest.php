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

namespace Modules\Billable\Tests\Feature;

use Modules\Core\Tests\ResourceTestCase;

class MarkProductAsActiveTest extends ResourceTestCase
{
    protected $uriKey = 'mark-as-active';

    protected $resourceName = 'products';

    public function test_super_admin_user_can_run_mark_as_active_action(): void
    {
        $this->signIn();
        $product = $this->factory()->inactive()->create();

        $this->runAction($this->uriKey, $product->id)->assertActionOk();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_authorized_user_can_run_mark_as_active_action(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all products')->signIn();

        $user = $this->createUser();
        $product = $this->factory()->inactive()->for($user, 'creator')->create();

        $this->runAction($this->uriKey, $product->id)->assertActionOk();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_unauthorized_user_can_run_mark_as_active_action_on_own_deal(): void
    {
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own products')->signIn();

        $productForSignedIn = $this->factory()->inactive()->for($signedInUser, 'creator')->create();
        $otherProduct = $this->factory()->inactive()->create();

        $this->runAction($this->uriKey, $otherProduct->id)->assertActionUnauthorized();
        $this->runAction($this->uriKey, $productForSignedIn->id);
        $this->assertTrue($productForSignedIn->fresh()->is_active);
    }
}
