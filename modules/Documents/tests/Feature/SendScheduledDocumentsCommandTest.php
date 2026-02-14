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

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Documents\Console\Commands\SendScheduledDocuments;
use Modules\Documents\Mail\SendDocument;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentSigner;
use Tests\TestCase;

class SendScheduledDocumentsCommandTest extends TestCase
{
    public function test_it_sends_scheduled_documents(): void
    {
        Mail::fake();

        Carbon::setTestNow('2023-03-21 12:00:00');

        $initiative = $this->createUser();

        $document = Document::factory()
            ->signable()
            ->mailable($initiative->id)
            ->has(DocumentSigner::factory()->mailable(), 'signers')
            ->has(DocumentSigner::factory(), 'signers')
            ->hasRecipients([
                ['name' => 'John Doe', 'email' => 'john@example.com', 'send_email' => true],
                ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'send_email' => false],
            ])
            ->create([
                'send_at' => '2023-03-21 12:05:00',
            ]);

        $mailableSigner = $document->signers->firstWhere('send_email', true);

        $this->artisan(SendScheduledDocuments::class);

        Mail::assertNothingSent();
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'send_at' => '2023-03-21 12:05:00']);

        Carbon::setTestNow('2023-03-21 12:05:01');
        $this->artisan(SendScheduledDocuments::class);

        $this->assertDatabaseHas('documents', ['id' => $document->id, 'send_at' => null]);

        $this->assertDatabaseHas('document_signers', [
            'id' => $mailableSigner->id,
            'send_email' => false,
        ]);

        Mail::assertSent(SendDocument::class, 2);

        $this->assertNotNull($mailableSigner->refresh()->sent_at);
        $this->assertNotNull($document->refresh()->data['recipients'][0]['sent_at']);
    }
}
