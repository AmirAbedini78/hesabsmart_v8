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

use Tests\TestCase;

class RetrieveTimezonesTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_timezones_endpoints(): void
    {
        $this->getJson('/api/timezones')->assertUnauthorized();
    }

    public function test_timezones_can_be_retrieved(): void
    {
        $this->signIn();

        $this->getJson('/api/timezones')->assertOk();
    }
}
