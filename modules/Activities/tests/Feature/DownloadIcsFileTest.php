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

use Modules\Core\Tests\ResourceTestCase;
use Modules\Users\Models\User;

class DownloadIcsFileTest extends ResourceTestCase
{
    protected $action = 'download-ics-file';

    protected $resourceName = 'activities';

    public function test_download_ics_file_action(): void
    {
        $this->signIn();

        $activity = $this->factory()->create();

        $this->runAction($this->action, $activity)
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename='.$activity->icsFilename().'.ics');
    }

    public function test_authorized_user_can_run_download_ics_file_action(): void
    {
        $this->asRegularUser()->withPermissionsTo('view all activities')->signIn();

        $activity = $this->factory()->has(User::factory())->create();

        $this->runAction($this->action, $activity)
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename='.$activity->icsFilename().'.ics');
    }

    public function test_unauthorized_user_can_run_download_ics_file_action_on_own_activity(): void
    {
        $signedInUser = $this->asRegularUser()->withPermissionsTo('view own activities')->signIn();

        $activityForSignedIn = $this->factory()->for($signedInUser)->create();
        $otherActivity = $this->factory()->create();

        $this->runAction($this->action, $otherActivity)->assertActionUnauthorized();
        $this->runAction($this->action, $activityForSignedIn)
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename='.$activityForSignedIn->icsFilename().'.ics');

    }
}
