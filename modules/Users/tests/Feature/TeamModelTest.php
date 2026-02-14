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

use Modules\Users\Models\Team;
use Modules\Users\Models\User;
use Tests\TestCase;

class TeamModelTest extends TestCase
{
    public function test_team_has_users(): void
    {
        $team = Team::factory()->has(User::factory()->count(2))->create();

        $this->assertCount(2, $team->users);
    }
}
