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

namespace Modules\Contacts\Tests\Feature;

use Modules\Activities\Models\Activity;
use Modules\Calls\Models\Call;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Phone;
use Modules\Contacts\Models\Source;
use Modules\Core\Database\Seeders\CountriesSeeder;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Country;
use Modules\Core\Tests\ResourceTestCase;
use Modules\Deals\Models\Deal;
use Modules\Notes\Models\Note;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;

class ContactResourceTest extends ResourceTestCase
{
    protected $resourceName = 'contacts';

    protected $samplePayload = ['first_name' => 'John Doe'];

    public function test_user_can_create_contact(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();

        $user = $this->createUser();
        $source = Source::factory()->create();
        $company = Company::factory()->create();
        $deal = Deal::factory()->create();

        $response = $this->postJson($this->createEndpoint(), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phones' => [
                ['number' => '+123654-88-885', 'type' => 'work'],
                ['number' => '+123654-77-885', 'type' => 'mobile'],
                ['number' => '+123654-66-885', 'type' => 'other'],
                ['number' => '', 'type' => 'other'],
            ],
            'source_id' => $source->id,
            'country_id' => Country::first()->getKey(),
            'user_id' => $user->id,
            'deals' => [$deal->id],
            'companies' => [$company->id],
        ])
            ->assertCreated();

        $this->assertResourceJsonStructure($response);

        $response->assertJsonCount(1, 'companies')
            ->assertJsonCount(1, 'deals')
            ->assertJson([
                'companies' => [['id' => $company->id]],
                'deals' => [['id' => $deal->id]],
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phones' => [
                    ['number' => '+123654-88-885', 'type' => 'work'],
                    ['number' => '+123654-77-885', 'type' => 'mobile'],
                    ['number' => '+123654-66-885', 'type' => 'other'],
                ],
                'source_id' => $source->id,
                'source' => ['id' => $source->id],
                'user_id' => $user->id,
                'user' => ['id' => $user->id],
                'was_recently_created' => true,
                'display_name' => 'John Doe',
            ]);
    }

    public function test_user_can_update_contact(): void
    {
        $this->seed(CountriesSeeder::class);
        $user = $this->signIn();
        $record = $this->factory()->has(Phone::factory()->count(2), 'phones')
            ->has(Company::factory())->create();
        $source = Source::factory()->create();
        $company = Company::factory()->create();
        $deal = Deal::factory()->create();

        $response = $this->putJson($this->updateEndpoint($record), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phones' => [
                ['number' => '+136547-96636', 'type' => 'work'],
                ['number' => '+123654-88-885', 'type' => 'work'],
                ['number' => '+123654-77-885', 'type' => 'mobile'],
                ['number' => '+123654-66-885', 'type' => 'other'],
                ['number' => '', 'type' => 'other'],
            ],
            'source_id' => $source->id,
            'user_id' => $user->id,
            'deals' => [$deal->id],
            'companies' => [$company->id],
            'companies_count' => 1,
            'deals_count' => 1,
        ])
            ->assertOk();
        $this->assertResourceJsonStructure($response);

        $response->assertJsonCount(count($this->resource()->resolveActions(app(ResourceRequest::class))), 'actions')
            ->assertJsonCount(4, 'phones')
            ->assertJsonCount(1, 'companies')
            ->assertJsonCount(1, 'deals')
            ->assertJson([
                'phones' => [
                    ['number' => '+136547-96636', 'type' => 'work'],
                    ['number' => '+123654-88-885', 'type' => 'work'],
                    ['number' => '+123654-77-885', 'type' => 'mobile'],
                    ['number' => '+123654-66-885', 'type' => 'other'],
                ],
                'companies' => [['id' => $company->id]],
                'deals' => [['id' => $deal->id]],
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'source_id' => $source->id,
                'user_id' => $user->id,
                'display_name' => 'Jane Doe',
                'deals_count' => 1,
            ]);
    }

    public function test_user_can_retrieve_contacts(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_user_can_retrieve_contact(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_globally_search_contacts(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson("/api/search?q={$record->first_name}&only=contacts")
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/contacts/{$record->id}")
            ->assertJsonPath('0.data.0.display_name', $record->full_name);
    }

    public function test_an_unauthorized_user_can_global_search_only_own_contacts(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->signIn();
        $user1 = $this->createUser();

        $this->factory()->for($user1)->create(['first_name' => 'John Doe KONKORD']);
        $record = $this->factory()->for($user)->create(['first_name' => 'John Konkord']);

        $this->getJson('/api/search?q=John&only=contacts')
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/contacts/{$record->id}")
            ->assertJsonPath('0.data.0.display_name', $record->full_name);
    }

    public function test_user_can_search_emails_for_contacts(): void
    {
        $this->signIn();

        $record = $this->factory()->create(['email' => 'konkord@example.com']);

        $this->getJson('/api/search/email-address?q=konkord@example.com&only=contacts')
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/contacts/{$record->id}")
            ->assertJsonPath('0.data.0.address', 'konkord@example.com')
            ->assertJsonPath('0.data.0.resourceName', $this->resourceName)
            ->assertJsonPath('0.data.0.name', $record->full_name);
    }

    public function test_user_can_export_contacts(): void
    {
        $this->performExportTest();
    }

    public function test_user_can_create_contact_with_custom_fields(): void
    {
        $this->signIn();

        $response = $this->postJson($this->createEndpoint(), array_merge([
            'first_name' => 'John',
        ], $this->customFieldsPayload()))->assertCreated();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_update_contact_with_custom_fields(): void
    {
        $this->signIn();
        $record = $this->factory()->create();

        $response = $this->putJson($this->updateEndpoint($record), array_merge([
            'first_name' => 'John',
        ], $this->customFieldsPayload()))->assertOk();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_import_contacts(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();

        $this->performImportTest();
    }

    public function test_user_can_import_contacts_with_custom_fields(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();

        $this->performImportWithCustomFieldsTest();
    }

    public function test_user_properly_finds_duplicate_contacts_during_import_via_email(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();
        $this->factory()->create(['email' => 'duplicate@example.com']);

        $this->performImportWithDuplicateTest(['email' => 'duplicate@example.com']);
    }

    public function test_user_properly_finds_duplicate_contacts_during_import_via_phone(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();
        $this->factory()->has(Phone::factory()->state(['number' => '+1365-987-444']))->create();

        $this->performImportWithDuplicateTest(['phones' => '+1365-987-444']);
    }

    public function test_it_restores_trashed_duplicate_contact_during_import(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->signIn();

        $contact = $this->factory()->create(['email' => 'duplicate@example.com']);

        $contact->delete();

        $import = $this->performImportUpload($this->createFakeImportFile(
            [$this->createImportHeader(), $this->createImportRow(['email' => 'duplicate@example.com'])]
        ));

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertFalse($contact->fresh()->trashed());
    }

    public function test_company_is_automatically_associated_to_contact_by_email_domain(): void
    {
        $this->signIn();
        settings()->set('auto_associate_company_to_contact', true);
        Company::factory()->create(['domain' => 'concordcrm.com']);

        $this->postJson($this->createEndpoint(), [
            'first_name' => 'John',
            'email' => 'marjan@concordcrm.com',
        ])->assertCreated()->assertJsonCount(1, 'companies');
    }

    public function test_multiple_companies_can_be_automatically_associated_to_contact_by_email_domain(): void
    {
        $this->signIn();
        settings()->set('auto_associate_company_to_contact', true);
        Company::factory()->create(['domain' => 'concordcrm.com']);
        Company::factory()->create(['domain' => 'concordcrm.com']);

        $this->postJson($this->createEndpoint(), [
            'first_name' => 'John',
            'email' => 'marjan@concordcrm.com',
        ])->assertCreated()->assertJsonCount(2, 'companies');
    }

    public function test_it_does_associate_company_to_contact_by_email_domain_when_a_company_is_already_provided(): void
    {
        $this->signIn();
        settings()->set('auto_associate_company_to_contact', true);

        Company::factory()->create(['domain' => 'concordcrm.com']);
        $company = Company::factory()->create(['domain' => 'concordcrm.test']);

        $this->postJson($this->createEndpoint(), [
            'first_name' => 'John',
            'email' => 'marjan@concordcrm.com',
            'companies' => [$company->id],
        ])->assertCreated()->assertJsonCount(2, 'companies');
    }

    public function test_user_can_load_the_contacts_table(): void
    {
        $this->performTestTableLoad();
    }

    public function test_contacts_table_loads_all_fields(): void
    {
        $this->performTestTableCanLoadWithAllFields();
    }

    public function test_user_can_force_delete_contact(): void
    {
        $this->signIn();

        $record = $this->factory()
            ->has(Company::factory())
            ->has(Note::factory())
            ->has(Call::factory())
            ->has(Activity::factory())
            ->has(Deal::factory())
            ->create();

        $record->delete();

        $this->deleteJson($this->forceDeleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 0);
    }

    public function test_user_can_soft_delete_contact(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 1);
    }

    public function test_contact_can_be_viewed_without_own_permissions(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user)->create();

        $this->getJson($this->showEndpoint($record))->assertOk()->assertJson(['id' => $record->id]);
    }

    public function test_edit_all_contacts_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all contacts')->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload)->assertOk();
    }

    public function test_edit_own_contacts_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit own contacts')->signIn();
        $record1 = $this->factory()->for($user)->create();
        $record2 = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record1), $this->samplePayload)->assertOk();
        $this->putJson($this->updateEndpoint($record2), $this->samplePayload)->assertForbidden();
    }

    public function test_edit_team_contacts_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit team contacts')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser)->create();

        $this->putJson($this->updateEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_update_contact(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload)->assertForbidden();
    }

    public function test_view_all_contacts_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('view all contacts')->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_view_team_contacts_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view team contacts')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser)->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_view_own_contact(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user)->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_view_contact(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_delete_any_contact_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('delete any contact')->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function test_delete_own_contacts_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete own contacts')->signIn();

        $record1 = $this->factory()->for($user)->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_delete_team_contacts_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete team contacts')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record1 = $this->factory()->for($teamUser)->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_delete_contact(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->deleteJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_it_empties_contacts_trash(): void
    {
        $this->signIn();

        $this->factory()->count(2)->trashed()->create();

        $this->deleteJson('/api/trashed/contacts?limit=2')->assertJson(['deleted' => 2]);
        $this->assertDatabaseEmpty('contacts');
    }

    public function test_it_excludes_unauthorized_records_from_empty_contacts_trash(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own contacts', 'delete own contacts', 'bulk delete contacts'])->signIn();

        $this->factory()->trashed()->create();
        $this->factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/contacts')->assertJson(['deleted' => 1]);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_it_does_not_empty_contacts_trash_if_delete_own_contacts_permission_is_not_applied(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own contacts', 'bulk delete contacts'])->signIn();

        $this->factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/contacts')->assertJson(['deleted' => 0]);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_contact_has_view_route(): void
    {
        $model = $this->factory()->create();

        $this->assertEquals('/contacts/'.$model->id, $this->resource()->viewRouteFor($model));
    }

    public function test_contact_has_title(): void
    {
        $model = $this->factory()->make(['first_name' => 'Firstname', 'last_name' => 'Lastname']);

        $this->assertEquals('Firstname Lastname', $this->resource()->titleFor($model));
    }

    protected function assertResourceJsonStructure($response)
    {
        $response->assertJsonStructure([
            'actions', 'avatar', 'avatar_url', 'calls_count', 'city', 'companies', 'companies_count', 'country', 'country_id', 'created_at', 'deals', 'deals_count', 'display_name', 'email', 'first_name', 'guest_display_name', 'guest_email', 'id', 'job_title', 'last_name', 'media', 'next_activity_date', 'notes_count', 'owner_assigned_date', 'phones', 'postal_code', 'source', 'source_id', 'state', 'street', 'timeline_subject_key', 'incomplete_activities_for_user_count', 'unread_emails_for_user_count', 'updated_at', 'uploaded_avatar_url', 'path', 'user', 'user_id', 'was_recently_created', 'tags',
            'authorizations' => [
                'create', 'delete', 'update', 'view', 'viewAny',
            ],
        ]);
    }
}
