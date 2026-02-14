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

namespace Modules\Core\Tests\Feature\Controller\Api;

use Illuminate\Support\Facades\Artisan;
use Modules\Core\Facades\MailableTemplates;
use Tests\TestCase;

class ExecuteToolTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_system_tools_endpoints(): void
    {
        $this->postJson('/api/tools/clear-cache')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_system_tools_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/tools/clear-cache')->assertForbidden();
    }

    public function test_storage_link_tool_can_be_executed(): void
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with('storage:link', ['--force' => true]);

        $this->postJson('/api/tools/storage-link')->assertNoContent();
    }

    public function test_clear_cache_tool_can_be_executed(): void
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with(config('core.commands.clear-cache', 'optimize:clear'), []);

        $this->postJson('/api/tools/clear-cache')->assertNoContent();
    }

    public function test_optimize_tool_can_be_executed(): void
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with(config('core.commands.optimize', 'optimize'), []);

        $this->postJson('/api/tools/optimize')->assertNoContent();
    }

    public function test_seed_mailable_templates_tool_can_be_executed(): void
    {
        $this->signIn();

        MailableTemplates::spy();

        $this->postJson('/api/tools/seed-mailable-templates')->assertOk();

        MailableTemplates::shouldHaveReceived('seed')->once();
    }
}
