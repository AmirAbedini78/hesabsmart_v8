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

namespace Modules\Deals\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Modules\Activities\Models\Activity;
use Modules\Billable\Models\Billable;
use Modules\Billable\Models\BillableProduct;
use Modules\Calls\Models\Call;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\ModelVisibilityGroup;
use Modules\Core\Tests\ResourceTestCase;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Events\DealMovedToStage;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;
use Modules\Notes\Models\Note;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;

class DealResourceTest extends ResourceTestCase
{
    protected $resourceName = 'deals';

    public function test_user_can_create_deal(): void
    {
        $this->signIn();
        $user = $this->createUser();
        $pipeline = Pipeline::factory()->withStages()->create();
        $stage = $pipeline->stages->first();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->postJson($this->createEndpoint(), [
            'name' => 'Deal Name',
            'expected_close_date' => $closeDate = now()->addMonth()->format('Y-m-d'),
            'pipeline_id' => $pipeline->id,
            'amount' => 1250,
            'stage_id' => $stage->id,
            'user_id' => $user->id,
            'companies' => [$company->id],
            'contacts' => [$contact->id],
        ])
            ->assertCreated();

        $this->assertResourceJsonStructure($response);
        $response->assertJsonCount(1, 'companies')
            ->assertJsonCount(1, 'contacts')
            ->assertJson([
                'companies' => [['id' => $company->id]],
                'contacts' => [['id' => $contact->id]],
                'name' => 'Deal Name',
                'expected_close_date' => Carbon::parse($closeDate)->toJSON(),
                'pipeline_id' => (string) $pipeline->id,
                'amount' => (string) 1250,
                'stage_id' => (string) $stage->id,
                'user_id' => (string) $user->id,
                'was_recently_created' => true,
                'display_name' => 'Deal Name',
                'companies_count' => 1,
                'contacts_count' => 1,
            ]);
    }

    public function test_user_can_update_deal(): void
    {
        $user = $this->signIn();
        $pipeline = Pipeline::factory()->withStages()->create();
        $stage = $pipeline->stages->first();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create();
        $record = $this->factory()->has(Company::factory())->create();

        $response = $this->putJson($this->updateEndpoint($record), [
            'name' => 'Deal Name',
            'expected_close_date' => $closeDate = now()->addMonth()->format('Y-m-d'),
            'pipeline_id' => $pipeline->id,
            'amount' => 3655,
            'stage_id' => $stage->id,
            'user_id' => $user->id,
            'companies' => [$company->id],
            'contacts' => [$contact->id],
        ])
            ->assertOk();

        $this->assertResourceJsonStructure(($response));

        $response->assertJsonCount(count($this->resource()->resolveActions(app(ResourceRequest::class))), 'actions')
            ->assertJsonCount(1, 'companies')
            ->assertJsonCount(1, 'contacts')
            ->assertJson([
                'companies' => [['id' => $company->id]],
                'contacts' => [['id' => $contact->id]],
                'name' => 'Deal Name',
                'expected_close_date' => Carbon::parse($closeDate)->toJSON(),
                'pipeline_id' => (string) $pipeline->id,
                'amount' => (string) 3655,
                'stage_id' => (string) $stage->id,
                'user_id' => (string) $user->id,
                'display_name' => 'Deal Name',
                'companies_count' => 1,
                'contacts_count' => 1,
            ]);
    }

    public function test_user_can_retrieve_deals(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_user_can_retrieve_deal(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_globally_search_deals(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson("/api/search?q={$record->name}&only=deals")
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/deals/{$record->id}")
            ->assertJsonPath('0.data.0.display_name', $record->name);
    }

    public function test_an_unauthorized_user_can_global_search_only_deals(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own deals')->signIn();
        $user1 = $this->createUser();

        $this->factory()->for($user1)->create(['name' => 'DEAL KONKORD']);
        $record = $this->factory()->for($user)->create(['name' => 'DEAL INOKLAPS']);

        $this->getJson('/api/search?q=DEAL&only=deals')
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/deals/{$record->id}")
            ->assertJsonPath('0.data.0.display_name', $record->name);
    }

    public function test_user_can_export_deals(): void
    {
        $this->performExportTest();
    }

    public function test_user_can_create_deal_with_custom_fields(): void
    {
        $this->signIn();

        $response = $this->postJson(
            $this->createEndpoint(),
            array_merge($this->samplePayload(), $this->customFieldsPayload())
        )->assertCreated();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_update_deal_with_custom_fields(): void
    {
        $this->signIn();
        $record = $this->factory()->create();

        $response = $this->putJson(
            $this->updateEndpoint($record),
            array_merge($this->samplePayload(), $this->customFieldsPayload())
        )->assertOk();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_import_deals(): void
    {
        $this->signIn();

        $this->performImportTest();
    }

    public function test_user_can_import_deals_with_custom_fields(): void
    {
        $this->signIn();

        $this->performImportWithCustomFieldsTest();
    }

    protected function performImportTest($overrides = []): void
    {
        Pipeline::factory()->withStages()->create();
        parent::performExportTest($overrides);
    }

    protected function performImportWithCustomFieldsTest(): void
    {
        Pipeline::factory()->withStages()->create();
        parent::performImportWithCustomFieldsTest();
    }

    protected function importEndpoint($import): string
    {
        $id = is_int($import) ? $import : $import->getKey();
        $pipeline = Pipeline::first();

        return "/api/{$this->resourceName}/import/{$id}?pipeline_id={$pipeline->id}";
    }

    public function test_user_can_load_the_deals_table(): void
    {
        $this->performTestTableLoad();
    }

    public function test_deals_table_loads_all_fields(): void
    {
        $this->performTestTableCanLoadWithAllFields();
    }

    public function test_it_doesnt_create_deal_with_restricted_visibility_pipeline(): void
    {
        $this->asRegularUser()->signIn();

        $pipeline = $this->newPipelineFactoryWithVisibilityGroup('users', User::factory())->create();

        $this->postJson(
            $this->createEndpoint(),
            ['pipeline_id' => $pipeline->id]
        )
            ->assertJsonValidationErrors(['pipeline_id' => 'This Pipeline value is forbidden.']);
    }

    public function test_it_doesnt_update_deal_with_restricted_visibility_pipeline(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all deals')->signIn();
        $deal = $this->factory()->create();
        $pipeline = $this->newPipelineFactoryWithVisibilityGroup('users', User::factory())->create();

        $this->putJson(
            $this->updateEndpoint($deal),
            ['pipeline_id' => $pipeline->id]
        )->assertJsonValidationErrors(['pipeline_id' => 'This Pipeline value is forbidden.']);
    }

    public function test_stage_id_is_required_when_pipeline_is_provided(): void
    {
        $this->signIn();
        $deal = $this->factory()->create();

        $this->putJson(
            $this->updateEndpoint($deal),
            [
                'pipeline_id' => $deal->pipeline_id,
                'name' => 'Changed Name',
            ]
        )
            ->assertJsonValidationErrorFor('stage_id');
    }

    public function test_user_can_update_deal_with_same_restricted_visibility_pipeline(): void
    {
        $this->asRegularUser()->withPermissionsTo(['view all deals', 'edit all deals'])->signIn();
        $pipeline = $this->newPipelineFactoryWithVisibilityGroup('users', User::factory())->withStages()->create();
        $deal = $this->factory()->for($pipeline)->create();

        $this->putJson(
            $this->updateEndpoint($deal),
            [
                'pipeline_id' => $pipeline->id,
                'stage_id' => $deal->stage_id,
                'name' => 'Changed Name',
            ]
        )
            ->assertOk()
            ->assertJson([
                'pipeline_id' => $pipeline->id,
                'name' => 'Changed Name',
            ]);
    }

    public function test_it_updates_stage_when_the_pipeline_is_restricted(): void
    {
        $this->asRegularUser()->withPermissionsTo(['edit all deals'])->signIn();
        $pipeline = $this->newPipelineFactoryWithVisibilityGroup('users', User::factory())->withStages()->create();
        $deal = $this->factory()->for($pipeline)->create();
        $stage = Stage::factory()->for($pipeline)->create();

        $this->putJson($this->updateEndpoint($deal), ['stage_id' => $stage->id])
            ->assertOk()
            ->assertJson(['stage_id' => $stage->id]);
    }

    public function test_when_creating_it_uses_stage_pipeline_when_pipeline_is_not_provided(): void
    {
        $this->signIn();

        $stage = Stage::factory()->create();

        $this->postJson(
            $this->createEndpoint(),
            [
                'name' => 'Deal Name',
                'stage_id' => $stage->id,
            ]
        );

        $deal = Deal::first();

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_deal_is_by_default_open(): void
    {
        $this->signIn();

        $stage = Stage::factory()->create();

        $this->postJson(
            $this->createEndpoint(),
            [
                'name' => 'Deal Name',
                'stage_id' => $stage->id,
            ]
        )->assertJson(['status' => DealStatus::open->name]);
    }

    public function test_status_can_be_provided_when_creating_new_deal(): void
    {
        $this->signIn();

        $stage = Stage::factory()->create();

        $this->postJson(
            $this->createEndpoint(),
            [
                'name' => 'Deal Name',
                'stage_id' => $stage->id,
                'status' => DealStatus::lost->name,
            ]
        )->assertJson(['status' => DealStatus::lost->name]);
    }

    public function test_status_can_be_provided_when_updating_a_deal(): void
    {
        $this->signIn();

        $deal = Deal::factory()->open()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
            'lost_reason' => 'Lost Reason',
        ])->assertJson(['status' => DealStatus::lost->name, 'lost_reason' => 'Lost Reason']);
    }

    public function test_it_starts_stage_history_when_deal_status_is_open(): void
    {
        $this->signIn();

        $stage = Stage::factory()->create();

        $this->postJson(
            $this->createEndpoint(),
            [
                'name' => 'Deal Name',
                'stage_id' => $stage->id,
                'status' => DealStatus::open->name,
            ]
        );

        $deal = Deal::first();

        $this->assertCount(1, $deal->stagesHistory);

        $deal->markAsLost();

        $this->putJson(
            $this->updateEndpoint($deal),
            [
                'status' => DealStatus::open->name,
            ]
        );

        $this->assertCount(2, $deal->fresh()->stagesHistory);
    }

    public function test_it_requires_an_open_deal_to_mark_as_lost(): void
    {
        $this->signIn();

        $deal = Deal::factory()->won()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
        ])->assertStatusConflict();
    }

    public function test_it_requires_an_open_deal_to_mark_as_won(): void
    {
        $this->signIn();

        $deal = Deal::factory()->lost()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::won->name,
        ])->assertStatusConflict();
    }

    public function test_it_does_not_update_lost_reason_when_marking_deal_as_won(): void
    {
        $this->signIn();

        $deal = Deal::factory()->open()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::won->name,
            'lost_reason' => 'Lost Reason',
        ])->assertJson(['lost_reason' => null]);
    }

    public function test_it_does_not_update_lost_reason_when_marking_deal_as_open(): void
    {
        $this->signIn();

        $deal = Deal::factory()->won()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::open->name,
            'lost_reason' => 'Lost Reason',
        ])->assertJson(['lost_reason' => null]);
    }

    public function test_it_does_not_trigger_errors_when_closed_deal_same_status_is_provided(): void
    {
        $this->signIn();

        $deal = Deal::factory()->lost()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'name' => 'Updated Name',
            'status' => DealStatus::lost->name,
        ])->assertOk()->assertJson([
            'name' => 'Updated Name',
            'status' => DealStatus::lost->name,
        ]);
    }

    public function test_lost_reason_can_be_updated_when_deal_is_lost(): void
    {
        // api usage only
        $this->signIn();

        $deal = Deal::factory()->lost('Original Lost Reason')->create();

        $this->putJson($this->updateEndpoint($deal), [
            'lost_reason' => 'Changed Lost Reason',
        ])->assertOk()->assertJson([
            'lost_reason' => 'Changed Lost Reason',
        ]);
    }

    public function test_lost_reason_can_be_optional(): void
    {
        $this->signIn();
        settings(['lost_reason_is_required' => false]);

        $deal = Deal::factory()->open()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
            'lost_reason' => '',
        ])->assertJsonMissingValidationErrors('lost_reason');

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
            'lost_reason' => null,
        ])->assertJsonMissingValidationErrors('lost_reason');

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
        ])->assertJsonMissingValidationErrors('lost_reason');
    }

    public function test_lost_reason_can_be_required(): void
    {
        $this->signIn();
        settings(['lost_reason_is_required' => true]);

        $deal = Deal::factory()->open()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
            'lost_reason' => '',
        ])->assertJsonValidationErrorFor('lost_reason');

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
            'lost_reason' => null,
        ])->assertJsonValidationErrorFor('lost_reason');

        $this->putJson($this->updateEndpoint($deal), [
            'status' => DealStatus::lost->name,
        ])->assertJsonValidationErrorFor('lost_reason');
    }

    public function test_it_does_not_update_only_pipeline(): void
    {
        $pipeline = Pipeline::factory()->has(Stage::factory())->create();

        $deal = Deal::factory()->create();

        $this->putJson($this->updateEndpoint($deal), [
            'pipeline_id' => $pipeline->id,
        ]);

        $deal->refresh();

        $this->assertNotEquals($pipeline->id, $deal->pipeline_id);
    }

    public function test_when_updating_it_uses_stage_pipeline_when_pipeline_is_not_provided(): void
    {
        $pipeline = Pipeline::factory()->has(Stage::factory())->create();

        $deal = Deal::factory([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $pipeline->stages[0]->id,
        ])->create();

        $this->putJson($this->updateEndpoint($deal), [
            'pipeline_id' => null,
            'stage_id' => $deal->stage_id,
        ]);

        $deal->refresh();

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_when_creating_it_uses_stage_pipeline_when_provided_pipeline_id_does_not_belong_to_the_stage(): void
    {
        $this->signIn();

        $otherPipeline = Pipeline::factory()->create();
        $mainPipeline = Pipeline::factory()->has(Stage::factory())->create();

        $this->postJson($this->createEndpoint(), [
            'name' => 'Deal Name',
            'pipeline_id' => $otherPipeline->id,
            'stage_id' => $mainPipeline->stages[0]->id,
        ]);

        $deal = Deal::first();

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_when_updating_it_uses_stage_pipeline_id_when_provided_pipeline_id_does_not_belong_to_the_stage(): void
    {
        $this->signIn();

        $otherPipeline = Pipeline::factory()->create();
        $deal = Deal::factory()->for(Pipeline::factory()->has(Stage::factory()))->create();

        $this->putJson($this->updateEndpoint($deal), [
            'pipeline_id' => $otherPipeline->id,
            'stage_id' => $deal->pipeline->stages[0]->id,
        ]);

        $deal->refresh();

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_moved_to_stage_event_is_triggered_when_deal_stage_is_updated(): void
    {
        $this->signIn();

        $deal = Deal::factory()->create();
        $stageId = Stage::where('id', '!=', $deal->stage_id)->first()->id;

        Event::fake();

        $this->putJson($this->updateEndpoint($deal), [
            'stage_id' => $stageId,
        ]);

        Event::assertDispatched(DealMovedToStage::class);
    }

    public function test_stage_moved_activity_is_logged_when_deal_stage_is_updated(): void
    {
        $this->signIn();

        $deal = Deal::factory()->create();
        $stageId = Stage::where('id', '!=', $deal->stage_id)->first()->id;

        $this->putJson($this->updateEndpoint($deal), [
            'stage_id' => $stageId,
        ]);

        $latestActivity = $deal->changelog()->orderBy('id', 'desc')->first();
        $this->assertStringContainsString('deals::deal.timeline.stage.moved', (string) $latestActivity->properties);
    }

    public function test_user_can_force_delete_deal(): void
    {
        $this->signIn();

        $record = $this->factory()
            ->has(Contact::factory())
            ->has(Company::factory())
            ->has(Note::factory())
            ->has(Call::factory())
            ->has(Activity::factory())
            ->create();

        Billable::factory()
            ->withBillableable($record)
            ->has(BillableProduct::factory(), 'products')
            ->create();

        $record->delete();

        $this->deleteJson($this->forceDeleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 0);
        $this->assertDatabaseCount('billables', 0);
    }

    public function test_user_can_soft_delete_deal(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 1);
    }

    public function test_deal_can_be_viewed_without_own_permissions(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user)->create();

        $this->getJson($this->showEndpoint($record))->assertOk()->assertJson(['id' => $record->id]);
    }

    public function test_edit_all_deals_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all deals')->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload())->assertOk();
    }

    public function test_edit_own_deals_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit own deals')->signIn();
        $record1 = $this->factory()->for($user)->create();
        $record2 = $this->factory()->create();

        $payload = $this->samplePayload();
        $this->putJson($this->updateEndpoint($record1), $payload)->assertOk();
        $this->putJson($this->updateEndpoint($record2), $payload)->assertForbidden();
    }

    public function test_edit_team_deals_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit team deals')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser)->create();

        $this->putJson($this->updateEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_update_deal(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload())->assertForbidden();
    }

    public function test_view_all_deals_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('view all deals')->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_view_team_deals_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view team deals')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser)->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_view_own_deal(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user)->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_view_deal(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_delete_any_deal_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('delete any deal')->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function test_delete_own_deals_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete own deals')->signIn();

        $record1 = $this->factory()->for($user)->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_delete_team_deals_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete team deals')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record1 = $this->factory()->for($teamUser)->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_delete_deal(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->deleteJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_it_empties_deals_trash(): void
    {
        $this->signIn();

        $this->factory()->count(2)->trashed()->create();

        $this->deleteJson('/api/trashed/deals?limit=2')->assertJson(['deleted' => 2]);
        $this->assertDatabaseEmpty('deals');
    }

    public function test_it_excludes_unauthorized_records_from_empty_deals_trash(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own deals', 'delete own deals', 'bulk delete deals'])->signIn();

        $this->factory()->trashed()->create();
        $this->factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/deals')->assertJson(['deleted' => 1]);
        $this->assertDatabaseCount('deals', 1);
    }

    public function test_it_does_not_empty_deals_trash_if_delete_own_deals_permission_is_not_applied(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own deals', 'bulk delete deals'])->signIn();

        $this->factory()->trashed()->for($user)->create();

        $this->deleteJson('/api/trashed/deals')->assertJson(['deleted' => 0]);
        $this->assertDatabaseCount('deals', 1);
    }

    public function test_deal_has_view_route(): void
    {
        $model = $this->factory()->create();

        $this->assertEquals('/deals/'.$model->id, $this->resource()->viewRouteFor($model));
    }

    public function test_deal_has_title(): void
    {
        $model = $this->factory()->make(['name' => 'Deal Name']);

        $this->assertEquals('Deal Name', $this->resource()->titleFor($model));
    }

    protected function samplePayload()
    {
        $pipeline = Pipeline::factory()->withStages()->create();
        $stage = $pipeline->stages->first();

        return [
            'name' => 'Deal Name',
            'expected_close_date' => now()->addMonth()->format('Y-m-d'),
            'pipeline_id' => $pipeline->id,
            'amount' => 1250,
            'stage_id' => $stage->id,
        ];
    }

    protected function newPipelineFactoryWithVisibilityGroup($group, $attached)
    {
        return Pipeline::factory()->has(
            ModelVisibilityGroup::factory()->{$group}()->hasAttached($attached),
            'visibilityGroup'
        );
    }

    protected function assertResourceJsonStructure($response)
    {
        $response->assertJsonStructure([
            'actions', 'amount', 'board_order', 'calls_count', 'companies', 'companies_count', 'contacts', 'contacts_count', 'created_at', 'display_name', 'expected_close_date', 'id', 'media', 'name', 'next_activity_date', 'notes_count', 'owner_assigned_date', 'pipeline', 'pipeline_id', 'stage', 'stage_changed_date', 'stage_id', 'status', 'time_in_stages', 'timeline_subject_key', 'incomplete_activities_for_user_count', 'unread_emails_for_user_count', 'updated_at', 'path', 'user', 'user_id', 'was_recently_created', 'tags', 'authorizations' => [
                'create', 'delete', 'update', 'view', 'viewAny',
            ],
        ]);

        if ($response->getData()->status == DealStatus::won->name) {
            $response->assertResourceJsonStructure(['won_date']);
        }

        if ($response->getData()->status == DealStatus::lost->name) {
            $response->assertResourceJsonStructure(['lost_date', 'lost_reason']);
        }
    }
}
