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

use Illuminate\Support\Facades\Notification;
use Modules\Activities\Models\Activity;
use Modules\Activities\Notifications\ActivityReminder;
use Tests\TestCase;

class SendOverdueNotificationsCommandTest extends TestCase
{
    public function test_activities_notifications_command(): void
    {
        Notification::fake();

        $activity = Activity::factory()->create([
            'due_date' => date('Y-m-d', strtotime('+29 minutes')),
            'due_time' => date('H:i:s', strtotime('+29 minutes')),
            'reminder_minutes_before' => 30,
        ]);

        $this->artisan('activities:notify')->assertSuccessful();

        Notification::assertSentTo($activity->user, ActivityReminder::class);
        Notification::assertSentToTimes($activity->user, ActivityReminder::class, 1);
        $this->assertNotNull($activity->fresh()->reminded_at);
    }
}
