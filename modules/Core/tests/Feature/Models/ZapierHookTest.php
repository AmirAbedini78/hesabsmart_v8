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

namespace Modules\Core\Tests\Feature\Models;

use Modules\Core\Models\ZapierHook;
use Modules\Users\Models\User;
use Tests\TestCase;

class ZapierHookTest extends TestCase
{
    public function test_zapier_hook_has_user(): void
    {
        $user = $this->createUser();

        $hook = new ZapierHook([
            'hook' => 'created',
            'action' => 'create',
            'resource_name' => 'resource',
            'user_id' => $user->id,
            'zap_id' => 123,
        ]);

        $hook->save();

        $this->assertInstanceOf(User::class, $hook->user);
    }
}
