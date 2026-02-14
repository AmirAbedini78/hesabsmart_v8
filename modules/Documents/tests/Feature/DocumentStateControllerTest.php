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

use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Models\Document;
use Tests\TestCase;

class DocumentStateControllerTest extends TestCase
{
    public function test_user_can_mark_document_as_lost(): void
    {
        $this->signIn();

        $document = Document::factory()->draft()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertOk()->assertJson([
            'status' => DocumentStatus::LOST->value,
        ]);
    }

    public function test_unauthorized_user_cannot_mark_document_as_lost(): void
    {
        $this->asRegularUser()->signIn();

        $document = Document::factory()->draft()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertForbidden();
    }

    public function test_authorized_user_can_mark_document_as_lost(): void
    {
        $this->asRegularUser()->withPermissionsTo(['edit all documents'])->signIn();
        $document = Document::factory()->draft()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertOk();
    }

    public function test_authorized_user_can_mark_own_document_as_lost(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['edit own documents'])->signIn();
        $document = Document::factory()->for($user)->draft()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertOk();
    }

    public function test_it_fails_when_marking_already_lost_document_as_lost(): void
    {
        $this->signIn();

        $document = Document::factory()->lost()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertStatusConflict();
    }

    public function test_it_fails_when_marking_accepted_document_as_lost(): void
    {
        $this->signIn();

        $document = Document::factory()->accepted()->create();

        $this->postJson("/api/documents/$document->id/lost")->assertStatusConflict();
    }

    public function test_user_can_mark_document_as_accepted(): void
    {
        $this->signIn();

        $document = Document::factory()->draft()->create();

        $this->postJson("/api/documents/$document->id/accept")->assertOk()->assertJson([
            'status' => DocumentStatus::ACCEPTED->value,
        ]);
    }

    public function test_authorized_user_can_mark_document_as_accepted(): void
    {
        $this->asRegularUser()->withPermissionsTo(['edit all documents'])->signIn();
        $document = Document::factory()->draft()->create();

        $this->postJson("/api/documents/$document->id/accept")->assertOk();
    }

    public function test_authorized_user_can_mark_own_document_as_accepted(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['edit own documents'])->signIn();
        $document = Document::factory()->for($user)->draft()->create();

        $this->postJson("/api/documents/$document->id/accept")->assertOk();
    }

    public function test_it_fails_when_marking_already_accepted_document_as_accepted(): void
    {
        $this->signIn();

        $document = Document::factory()->accepted()->create();

        $this->postJson("/api/documents/$document->id/accept")->assertStatusConflict();
    }

    public function test_user_can_mark_document_as_draft(): void
    {
        $this->signIn();

        $document = Document::factory()->lost()->create();

        $this->postJson("/api/documents/$document->id/draft")->assertOk()->assertJson([
            'status' => DocumentStatus::DRAFT->value,
        ]);
    }

    public function test_authorized_user_can_mark_document_as_draft(): void
    {
        $this->asRegularUser()->withPermissionsTo(['edit all documents'])->signIn();
        $document = Document::factory()->lost()->create();

        $this->postJson("/api/documents/$document->id/draft")->assertOk();
    }

    public function test_authorized_user_can_mark_own_document_as_draft(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['edit own documents'])->signIn();
        $document = Document::factory()->for($user)->lost()->create();

        $this->postJson("/api/documents/$document->id/draft")->assertOk();
    }

    public function test_document_accepted_by_customer_cannot_be_marked_as_draft(): void
    {
        $this->signIn();

        $document = Document::factory()->accepted()->create(['marked_accepted_by' => null]);

        $this->postJson("/api/documents/$document->id/draft")
            ->assertStatusConflict()
            ->assertSeeText('Documents signed/accepted by customers cannot be marked as draft.');
    }
}
