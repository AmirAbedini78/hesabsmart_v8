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

namespace Modules\Core\Tests\Feature;

use Modules\Core\Criteria\VisibleModelsCriteria;
use Modules\Core\Models\ModelVisibilityGroup;
use Modules\Deals\Models\Pipeline;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;
use Tests\TestCase;

class VisibleModelsCriteriaTest extends TestCase
{
    public function test_visible_pipelines_criteria(): void
    {
        $user = User::factory()->has(Team::factory())->create();

        Pipeline::factory()
            ->has(
                ModelVisibilityGroup::factory()->teams()->hasAttached($user->teams->first()),
                'visibilityGroup'
            )
            ->create();

        $this->assertSame(1, Pipeline::criteria(new VisibleModelsCriteria($user))->count());
    }
}
