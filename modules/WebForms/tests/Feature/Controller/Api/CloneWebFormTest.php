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

namespace Modules\WebForms\Tests\Feature\Controller\Api;

use Illuminate\Testing\Fluent\AssertableJson;
use Modules\WebForms\Models\WebForm;
use Tests\TestCase;

class CloneWebFormTest extends TestCase
{
    public function test_unauthenticated_user_cannot_clone_web_form(): void
    {
        $form = WebForm::factory()->create();

        $this->postJson('/api/forms/'.$form->id.'/clone')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_clone_web_form(): void
    {
        $form = WebForm::factory()->create();

        $this->asRegularUser()->signIn();

        $this->postJson('/api/forms/'.$form->id.'/clone')->assertForbidden();
    }

    public function test_web_form_can_be_cloned(): void
    {
        $user = $this->signIn();

        $form = WebForm::factory()->create(['total_submissions' => 5]);

        $this->postJson('/api/forms/'.$form->id.'/clone')
            ->assertOk()
            ->assertJson([
                'total_submissions' => 0,
                'user_id' => $user->id,
            ])->assertJson(function (AssertableJson $json) use ($form) {
                $json->whereNot('uuid', $form->uuid)->etc();
            });

        $this->assertDatabaseHas('web_forms', ['created_by' => $user->id]);
    }
}
