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

namespace Modules\Users\Tests\Feature;

use Modules\Core\Tests\ResourceTestCase;
use Modules\Users\Models\User;

class UserDeleteTest extends ResourceTestCase
{
    protected $action = 'user-delete';

    protected $resourceName = 'users';

    public function test_super_admin_user_can_run_user_delete_action(): void
    {
        $this->signIn();

        $users = $this->createUser(2);

        $this->runAction($this->action, $users[0], ['user_id' => $users[1]->id])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $users[0]->id]);
    }

    public function test_non_super_admin_user_cant_run_user_delete_action(): void
    {
        $this->asRegularUser()->signIn();

        $users = $this->createUser(2);

        $this->runAction($this->action, $users[0], ['user_id' => $users[1]->id])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $users[0]->id]);
    }

    public function test_user_cannot_delete_his_own_account(): void
    {
        $user = $this->signIn();
        $user2 = User::factory()->create();

        $this->runAction($this->action, [$user2->id, $user->id], ['user_id' => $user->id])->assertStatusConflict();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    public function test_user_cannot_transfer_the_data_on_delete_on_the_same_user_which_is_about_to_be_deleted(): void
    {
        $this->signIn();

        $otherUser = $this->createUser();

        $this->runAction($this->action, $otherUser, ['user_id' => $otherUser->id])->assertStatusConflict();
        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }

    public function test_user_delete_action_requires_user_to_transfer_the_data_to(): void
    {
        $this->signIn();

        $this->runAction($this->action, [])->assertJsonValidationErrors(['user_id']);
    }
}
