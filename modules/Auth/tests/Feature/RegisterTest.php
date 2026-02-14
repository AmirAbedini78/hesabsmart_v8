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

namespace Modules\Auth\Tests\Feature;

use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_user_is_not_allowed_to_register(): void
    {
        $this->get('/register')->assertStatus(302);
        $this->post('/register')->assertStatus(405);
    }
}
