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

namespace Modules\Documents\Tests\Feature;

use Modules\Documents\Enums\DocumentViewType;
use Modules\Documents\Models\DocumentTemplate;
use Tests\TestCase;

class DocumentTemplateResourceTest extends TestCase
{
    public function test_user_can_create_document_template(): void
    {
        $user = $this->signIn();

        $this->postJson('/api/document-templates', [
            'name' => 'Template Name',
            'content' => 'Template Content',
            'view_type' => DocumentViewType::NAV_LEFT->value,
            'is_shared' => true,
        ])->assertCreated()->assertJson([
            'name' => 'Template Name',
            'content' => 'Template Content',
            'view_type' => DocumentViewType::NAV_LEFT->value,
            'is_shared' => true,
            'user_id' => $user->id,
        ]);
    }

    public function test_document_template_requires_name(): void
    {
        $this->signIn();

        $this->postJson('/api/document-templates', [
            'name' => '',
        ])->assertJsonValidationErrorFor('name');

        $template = DocumentTemplate::factory()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'name' => '',
        ])->assertJsonValidationErrorFor('name');
    }

    public function test_document_template_requires_content(): void
    {
        $this->signIn();

        $this->postJson('/api/document-templates', [
            'content' => '',
        ])->assertJsonValidationErrorFor('content');

        $template = DocumentTemplate::factory()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'content' => '',
        ])->assertJsonValidationErrorFor('content');
    }

    public function test_user_can_update_document_template(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'name' => 'Updated Template Name',
            'content' => 'Updated Template Content',
            'view_type' => DocumentViewType::NAV_LEFT->value,
            'is_shared' => true,
        ])->assertOk()->assertJson([
            'name' => 'Updated Template Name',
            'content' => 'Updated Template Content',
            'view_type' => DocumentViewType::NAV_LEFT->value,
            'is_shared' => true,
        ]);
    }

    public function test_admin_user_can_update_shared_template(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'name' => 'Updated Template Name',
            'content' => 'Updated Template Content',
        ])->assertOk();
    }

    public function test_regular_user_cant_update_shared_template(): void
    {
        $this->asRegularUser()->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'name' => 'Updated Template Name',
            'content' => 'Updated Template Content',
        ])->assertForbidden();
    }

    public function test_it_omits_the_user_id_attribute(): void
    {
        $currentUser = $this->signIn();

        $user = $this->createUser();

        $this->postJson('/api/document-templates', [
            'name' => 'Template Name',
            'content' => 'Template Content',
        ])->assertCreated()->assertJson([
            'user_id' => $currentUser->id,
        ]);

        $template = DocumentTemplate::factory()->create();

        $this->putJson("/api/document-templates/$template->id", [
            'name' => 'Updated Template Name',
            'content' => 'Updated Template Content',
            'user_id' => $user->id,
        ])->assertOk()->assertJson([
            'user_id' => $template->user_id,
        ]);
    }

    public function test_document_templates_can_be_retrieved(): void
    {
        $user = $this->signIn();

        DocumentTemplate::factory(2)->for($user)->create();
        DocumentTemplate::factory(2)->shared()->create();

        $this->getJson('/api/document-templates')->assertOk()->assertJsonCount(4, 'data');
    }

    public function test_user_can_see_only_authorized_templates(): void
    {
        $user = $this->signIn();

        DocumentTemplate::factory(1)->for($user)->create();

        DocumentTemplate::factory(1)->shared()->create();
        DocumentTemplate::factory(2)->create();

        $this->getJson('/api/document-templates')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_document_template_can_be_deleted(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->create();

        $this->deleteJson("/api/document-templates/$template->id")->assertNoContent();

        $this->assertDatabaseMissing('document_templates', ['id' => $template->id]);
    }

    public function test_admin_user_can_delete_shared_template(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->deleteJson("/api/document-templates/$template->id")->assertNoContent();

        $this->assertDatabaseMissing('document_templates', ['id' => $template->id]);
    }

    public function test_regular_user_cant_delete_shared_template(): void
    {
        $this->asRegularUser()->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->deleteJson("/api/document-templates/$template->id")->assertForbidden();

        $this->assertDatabaseHas('document_templates', ['id' => $template->id]);
    }

    public function test_document_template_can_be_cloned(): void
    {
        $user = $this->signIn();

        $template = DocumentTemplate::factory()->create();

        $this->postJson("/api/document-templates/$template->id/clone")
            ->assertOk()
            ->assertJson([
                'user_id' => $user->id,
            ]);

        $this->assertDatabaseCount('document_templates', 2);
    }

    public function test_it_marks_shared_cloned_template_as_not_shared(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->postJson("/api/document-templates/$template->id/clone")
            ->assertOk()
            ->assertJson([
                'is_shared' => false,
            ]);
    }

    public function test_document_template_can_be_retrieved(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->create();

        $this->getJson("/api/document-templates/$template->id")
            ->assertOk()
            ->assertJson([
                'name' => $template->name,
                'content' => $template->content,
                'user_id' => $template->user_id,
                'is_shared' => $template->is_shared,
            ]);
    }

    public function test_admin_user_can_retrieve_shared_template(): void
    {
        $this->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->getJson("/api/document-templates/$template->id")->assertOk();
    }

    public function test_regular_user_can_retrieve_shared_template(): void
    {
        $this->asRegularUser()->signIn();

        $template = DocumentTemplate::factory()->shared()->create();

        $this->getJson("/api/document-templates/$template->id")->assertOk();
    }

    public function test_regular_user_cant_retrieve_unauthorized_template(): void
    {
        $this->asRegularUser()->signIn();

        $template = DocumentTemplate::factory()->create();

        $this->getJson("/api/document-templates/$template->id")->assertForbidden();
    }
}
