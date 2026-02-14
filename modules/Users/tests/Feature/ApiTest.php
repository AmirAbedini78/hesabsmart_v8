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

use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_non_logged_in_user_cannot_access_api(): void
    {
        $this->createUser();

        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_authorized_user_can_access_api(): void
    {
        $this->signIn();

        $this->getJson('/api/users')->assertOk();
    }
}
