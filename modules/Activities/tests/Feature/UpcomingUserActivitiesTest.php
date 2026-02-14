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

use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\Activities\Cards\UpcomingUserActivities;
use Modules\Core\Tests\ResourceTestCase;

class UpcomingUserActivitiesTest extends ResourceTestCase
{
    protected $card;

    protected $resourceName = 'activities';

    protected function setUp(): void
    {
        parent::setUp();
        $this->card = new UpcomingUserActivities;
    }

    protected function tearDown(): void
    {
        unset($this->card);
        parent::tearDown();
    }

    public function test_my_activities_card(): void
    {
        $user = $this->withUserAttrs(['timezone' => 'UTC'])->signIn();

        Carbon::setTestNow(now()->startOfMonth());
        $dueDate = now();

        $activity = $this->factory()->inProgress()->for($user)->create([
            'due_date' => $dueDate->addWeek(1)->format('Y-m-d'),
            'due_time' => $dueDate->format('H:i:s'),
        ]);

        Carbon::setTestNow(now()->subMonth(1));

        $this->factory()->inProgress()->for($user)->create([
            'due_date' => now()->format('Y-m-d'),
            'due_time' => now()->format('H:i:s'),
        ]);

        Carbon::setTestNow(null);
        Carbon::setTestNow(now()->startOfMonth());
        $this->getJson("api/cards/{$this->card->uriKey()}?range=this_month")
            ->assertJsonCount(1, 'value.data')
            ->assertJson(function (AssertableJson $json) use ($activity) {
                $json->has('value.data.0', function ($json) use ($activity) {
                    $json->where('id', $activity->id)
                        ->where('title', $activity->title)
                        ->where('path', "/activities/{$activity->id}")
                        ->where('due_date', $activity->due_date)
                        ->has('authorizations')
                        ->etc();
                })->etc();
            });
    }

    public function test_upcoming_activities_card_shows_activities_for_logged_in_user_only(): void
    {
        $this->withUserAttrs(['timezone' => 'UTC'])->signIn();
        $user = $this->createUser();

        Carbon::setTestNow(now()->startOfMonth());

        $this->factory()->inProgress()->for($user)->create([
            'due_date' => now()->addWeek(1)->format('Y-m-d'),
        ]);

        $this->getJson("api/cards/{$this->card->uriKey()}?range=this_month")
            ->assertJsonCount(0, 'value.data');
    }

    public function test_it_does_not_query_the_completed_activities_on_upcoming_activities_card(): void
    {
        $user = $this->withUserAttrs(['timezone' => 'UTC'])->signIn();

        Carbon::setTestNow(now()->startOfMonth());

        $this->factory()->inProgress()->for($user)->create([
            'due_date' => now()->addWeek(1)->format('Y-m-d'),
        ]);

        $this->factory()->completed()->for($user)->create([
            'due_date' => now()->format('Y-m-d'),
        ]);

        $this->getJson("api/cards/{$this->card->uriKey()}?range=this_month")
            ->assertJsonCount(1, 'value.data');
    }
}
