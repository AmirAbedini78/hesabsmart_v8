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

class SystemControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_system_endpoints(): void
    {
        $this->getJson('/api/system/info')->assertUnauthorized();
        $this->postJson('/api/system/info')->assertUnauthorized();
        $this->getJson('/api/system/logs')->assertUnauthorized();
    }

    public function test_unauthorized_cannot_access_system_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/system/info')->assertForbidden();
        $this->postJson('/api/system/info')->assertForbidden();
        $this->getJson('/api/system/logs')->assertForbidden();
    }

    public function test_a_user_can_retrieve_system_info(): void
    {
        $this->signIn();

        $this->getJson('/api/system/info')->assertOk();
    }

    public function test_a_user_can_download_system_info(): void
    {
        $this->signIn();

        $this->postJson('/api/system/info')->assertDownload('system-info.xlsx');
    }

    public function test_a_user_can_retrieve_system_logs(): void
    {
        $this->signIn();

        $this->getJson('/api/system/logs')->assertOk();
    }
}
