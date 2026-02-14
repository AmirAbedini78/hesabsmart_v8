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

namespace Modules\Installer\Tests\Feature;

use Tests\TestCase;

class RequirementsControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_requirements_endpoints(): void
    {
        $this->get('/requirements')->assertRedirect(route('login'));
        $this->post('/requirements')->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_access_requirements_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->get('/requirements')->assertForbidden();
        $this->post('/requirements')->assertForbidden();
    }

    public function test_requirements_can_be_viewed(): void
    {
        $this->signIn();
        $this->get('/requirements')->assertSee('Required PHP Version');
    }

    public function test_requirements_can_be_confirmed(): void
    {
        settings()->set([
            '_app_url' => 'https://demo.concordcrm.com',
            '_server_ip' => 'server-ip',
        ])->save();

        $this->signIn();
        $this->post('/requirements');

        $this->assertEquals(settings()->get('_app_url'), config('app.url'));
    }
}
