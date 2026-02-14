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

class DataViewUserConfigControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_view_user_config_endpoints(): void
    {
        $this->postJson('/api/views/users/config/open')->assertUnauthorized();
        $this->postJson('/api/views/users/config/order')->assertUnauthorized();
    }

    public function test_it_updates_the_views_open_state(): void
    {
        $user = $this->signIn();
        $views = DataView::factory()->count(2)->for($user)->create();

        $this->postJson('/api/views/users/config/open', [
            $views[0]->id => true,
            $views[1]->id => false,
        ])->assertNoContent();

        $this->assertDatabaseHas('data_view_user_configs', [
            'user_id' => $user->id,
            'data_view_id' => $views[0]->id,
            'is_open' => true,
        ]);

        $this->assertDatabaseHas('data_view_user_configs', [
            'user_id' => $user->id,
            'data_view_id' => $views[1]->id,
            'is_open' => false,
        ]);
    }

    public function test_it_cannot_set_open_views_more_than_allowed(): void
    {
        $user = $this->signIn();

        config(['core.views.max_open' => 1]);

        $views = DataView::factory()->count(2)->for($user)->create();

        $this->postJson('/api/views/users/config/open', [
            $views[0]->id => true,
            $views[1]->id => true,
        ])->assertStatusConflict();
    }

    public function test_it_updates_the_views_order(): void
    {
        $user = $this->signIn();

        $views = DataView::factory()->count(2)->for($user)->create();

        $this->postJson('/api/views/users/config/order', [
            $views[0]->id => 1,
            $views[1]->id => 2,
        ])->assertNoContent();

        $this->assertDatabaseHas('data_view_user_configs', [
            'user_id' => $user->id,
            'data_view_id' => $views[0]->id,
            'display_order' => 1,
        ]);

        $this->assertDatabaseHas('data_view_user_configs', [
            'user_id' => $user->id,
            'data_view_id' => $views[1]->id,
            'display_order' => 2,
        ]);
    }
}
