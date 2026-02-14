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

namespace Modules\Updater\Tests\Feature;

use Tests\TestCase;

class FilePermissionErrorControllerTest extends TestCase
{
    public function test_file_permissions_can_be_viewed(): void
    {
        $this->signIn();

        $this->get('/update/errors/permissions')->assertOk();
    }
}
