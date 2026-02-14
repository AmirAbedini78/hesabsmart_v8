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

use Modules\Contacts\Models\Contact;
use Tests\TestCase;

class EmptyTrashTest extends TestCase
{
    public function test_user_can_empty_trash(): void
    {
        $this->signIn();

        Contact::factory()->count(2)->trashed()->create();

        $this->deleteJson('/api/trashed/contacts?limit=2')->assertJson(['deleted' => 2]);
        $this->assertDatabaseEmpty('contacts');
    }

    public function test_unauthorized_records_are_excluded_from_empty_trash(): void
    {
        $user = $this->asRegularUser()->signIn();

        Contact::factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/contacts')->assertJson(['deleted' => 0]);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_it_does_not_delete_records_if_bulk_delete_permission_is_not_applied(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own contacts', 'delete own contacts'])->signIn();

        Contact::factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/contacts')->assertJson(['deleted' => 0]);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_user_can_empty_trash_in_batches(): void
    {
        $this->signIn();

        Contact::factory()->count(2)->trashed()->create();

        $this->deleteJson('/api/trashed/contacts?limit=1')->assertJson(['deleted' => 1]);
        $this->assertDatabaseCount('contacts', 1);
        $this->deleteJson('/api/trashed/contacts?limit=1')->assertJson(['deleted' => 1]);
        $this->assertDatabaseEmpty('contacts');
    }
}
