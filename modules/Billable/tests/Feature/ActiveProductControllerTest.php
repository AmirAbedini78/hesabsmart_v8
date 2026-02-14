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

use Modules\Billable\Models\Product;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;
use Tests\TestCase;

class ActiveProductControllerTest extends TestCase
{
    public function test_active_products_can_be_retrieved(): void
    {
        $this->signIn();

        Product::factory()->active()->count(2)->create();
        Product::factory()->inactive()->create();

        $this->getJson('/api/products/active')->assertJsonCount(2);
    }

    public function test_active_products_with_view_all_products_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('view all products')->signIn();

        Product::factory()->active()->count(2)->create();

        $this->getJson('/api/products/active')->assertJsonCount(2);
    }

    public function test_active_products_with_view_own_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own products')->signIn();

        Product::factory()->active()->count(2)->create();
        Product::factory()->active()->for($user, 'creator')->create();

        $this->getJson('/api/products/active')->assertJsonCount(1);
    }

    public function test_active_products_with_view_team_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view team products')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        Product::factory()->active()->count(2)->create();
        Product::factory()->active()->for($teamUser, 'creator')->create();

        $this->getJson('/api/products/active')->assertJsonCount(1);
    }
}
