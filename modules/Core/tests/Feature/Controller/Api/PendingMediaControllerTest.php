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

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendingMediaControllerTest extends TestCase
{
    public function test_pending_media_can_be_stored(): void
    {
        $this->signIn();

        settings()->set('allowed_extensions', 'jpg');

        Storage::fake('local');

        $this->postJson('/api/media/pending/testDraftId', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertJson([
            'file_name' => 'photo1.jpg',
            'extension' => 'jpg',
            'disk_path' => 'pending-attachments/photo1.jpg',
            'was_recently_created' => true,
            'pending_data' => ['draft_id' => 'testDraftId'],
        ]);
    }

    public function test_pending_media_can_be_deleted(): void
    {
        $this->signIn();

        settings()->set('allowed_extensions', 'jpg');

        Storage::fake('local');

        $id = $this->postJson('/api/media/pending/testDraftId', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->pending_data->id;

        $this->deleteJson('/api/media/pending/'.$id)->assertNoContent();
        $this->assertDatabaseCount('pending_media_attachments', 0);
    }
}
