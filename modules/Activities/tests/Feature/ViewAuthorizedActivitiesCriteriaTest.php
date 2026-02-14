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

namespace Modules\Activities\Tests\Feature;

use Modules\Activities\Criteria\ViewAuthorizedActivitiesCriteria;
use Modules\Activities\Models\Activity;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;
use Tests\TestCase;

class ViewAuthorizedActivitiesCriteriaTest extends TestCase
{
    public function test_own_activities_criteria_queries_only_own_activities(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own activities')->createUser();

        Activity::factory()->for($user)->create();
        Activity::factory()->create();

        $this->signIn($user);
        $query = Activity::criteria(ViewAuthorizedActivitiesCriteria::class);
        $this->assertSame(1, $query->count());
    }

    public function test_it_returns_all_activities_when_user_is_authorized_to_see_all_activities(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view all activities')->createUser();

        Activity::factory()->for($user)->create();
        Activity::factory()->create();

        $this->signIn($user);
        $query = Activity::criteria(ViewAuthorizedActivitiesCriteria::class);
        $this->assertSame(2, $query->count());

        $this->signIn();
        $query = Activity::criteria(ViewAuthorizedActivitiesCriteria::class);
        $this->assertSame(2, $query->count());
    }

    public function test_it_retrieves_the_activities_where_user_attends_to_and_are_owned_by(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view attends and owned activities')->createUser();

        Activity::factory()->create();
        Activity::factory()->for($user)->create();

        $attendsActivity = Activity::factory()->create();
        $guest = $user->guests()->create([]);
        $guest->activities()->attach($attendsActivity);

        $this->signIn($user);
        $query = Activity::criteria(ViewAuthorizedActivitiesCriteria::class);
        $this->assertSame(2, $query->count());
    }

    public function test_it_retrieves_attends_and_owned_including_team_activities(): void
    {
        // Ticket #461
        $user = $this->asRegularUser()
            ->withPermissionsTo(['view attends and owned activities', 'view team activities'])
            ->createUser();

        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        Activity::factory()->for($teamUser)->create();
        Activity::factory()->for($user)->create();

        $attendsActivity = Activity::factory()->create();
        $guest = $user->guests()->create([]);
        $guest->activities()->attach($attendsActivity);

        $this->signIn($user);
        $query = Activity::criteria(ViewAuthorizedActivitiesCriteria::class);
        $this->assertSame(3, $query->count());
    }
}
