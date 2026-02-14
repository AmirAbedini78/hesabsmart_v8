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

class PermissionControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_permissions_endpoints(): void
    {
        $this->getJson('/api/permissions')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_permissions_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/permissions')->assertForbidden();
    }

    public function test_permissions_can_be_retrieved(): void
    {
        $this->signIn();

        $this->getJson('/api/permissions')->assertOk();
    }
}
