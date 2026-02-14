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

namespace Modules\Core\Tests\Feature\Controller\Api\Resource;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Contacts\Models\Contact;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    public function test_user_can_upload_media_file_to_resource(): void
    {
        $this->signIn();
        settings()->set('allowed_extensions', 'jpg');
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertJson([
            'file_name' => 'photo1.jpg',
            'extension' => 'jpg',
            'was_recently_created' => true,
        ]);
    }

    public function test_unauthorized_user_to_update_the_resource_cannot_upload_media_file(): void
    {
        $this->asRegularUser()->signIn();
        settings()->set('allowed_extensions', 'jpg');
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertForbidden();
    }

    public function test_user_can_delete_media_file_from_resource(): void
    {
        $this->signIn();
        settings()->set('allowed_extensions', 'jpg');
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $id = $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->id;

        $this->deleteJson('/api/contacts/'.$contact->id.'/media/'.$id)->assertNoContent();
        $this->assertDatabaseCount('media', 0);
    }

    public function test_unauthorized_user_to_update_the_resource_cannot_delete_media_file(): void
    {
        $this->signIn();
        settings()->set('allowed_extensions', 'jpg');
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $id = $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->id;

        $this->signIn($this->asRegularUser()->createUser());

        $this->deleteJson('/api/contacts/'.$contact->id.'/media/'.$id)->assertForbidden();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_media_cannot_be_uploaded_to_resource_that_does_not_accept_media(): void
    {
        $this->signIn();

        Storage::fake(config('mediable.default_disk'));

        $this->postJson('/api/fake-resource/fake-id/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertNotFound();
    }
}
