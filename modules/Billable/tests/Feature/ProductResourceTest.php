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

namespace Modules\Billable\Tests\Feature;

use Modules\Core\Tests\ResourceTestCase;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;

class ProductResourceTest extends ResourceTestCase
{
    protected $resourceName = 'products';

    protected $samplePayload = ['name' => 'Macbook Air', 'unit_price' => 1500];

    public function test_user_can_create_resource_record(): void
    {
        $this->signIn();

        $response = $this->postJson($this->createEndpoint(), $payload = [
            'name' => 'Macbook Pro',
            'description' => 'INTEL',
            'direct_cost' => 1250,
            'unit_price' => 1500,
            'is_active' => true,
            'sku' => 'MP-2018',
            'tax_label' => 'DDV',
            'tax_rate' => 18,
            'unit' => 'kg',
        ])
            ->assertCreated();

        $this->assertResourceJsonStructure($response);

        $response->assertJson($payload)
            ->assertJson([
                'was_recently_created' => true,
                'display_name' => 'Macbook Pro',
            ]);
    }

    public function test_user_can_update_resource_record(): void
    {
        $this->signIn();
        $record = $this->factory()->create();

        $response = $this->putJson($this->updateEndpoint($record), $payload = [
            'name' => 'Macbook Air',
            'description' => 'INTEL',
            'direct_cost' => 1250,
            'unit_price' => 1500,
            'is_active' => false,
            'sku' => 'MP-2018',
            'tax_label' => 'DDV',
            'tax_rate' => 18,
            'unit' => 'kg',
        ])
            ->assertOk();

        $this->assertResourceJsonStructure($response);

        $response->assertJson($payload)
            ->assertJson([
                'display_name' => 'Macbook Air',
            ]);
    }

    public function test_user_can_retrieve_resource_records(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_user_can_retrieve_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_globally_search_products(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson("/api/search?q={$record->name}&only=products")
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.display_name', $record->name);
    }

    public function test_an_unauthorized_user_can_global_search_only_own_records(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own products')->signIn();
        $user1 = $this->createUser();

        $this->factory()->for($user1, 'creator')->create(['name' => 'PRODUCT KONKORD']);
        $record = $this->factory()->for($user, 'creator')->create(['name' => 'PRODUCT INOKLAPS']);

        $this->getJson('/api/search?q=PRODUCT&only=products')
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', "/products/{$record->id}")
            ->assertJsonPath('0.data.0.display_name', $record->name);
    }

    public function test_user_can_export_products(): void
    {
        $this->performExportTest();
    }

    public function test_user_can_create_resource_record_with_custom_fields(): void
    {
        $this->signIn();

        $response = $this->postJson($this->createEndpoint(), array_merge([
            'name' => 'Macbook Pro',
            'unit_price' => 1500,
            'tax_label' => 'DDV',
            'tax_rate' => 18,
        ], $this->customFieldsPayload()))->assertCreated();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_update_resource_record_with_custom_fields(): void
    {
        $this->signIn();
        $record = $this->factory()->create();

        $response = $this->putJson($this->updateEndpoint($record), array_merge([
            'name' => 'Macbook Pro',
            'unit_price' => 1500,
            'tax_label' => 'DDV',
            'tax_rate' => 18,
        ], $this->customFieldsPayload()))->assertOk();

        $this->assertThatResponseHasCustomFieldsValues($response);
    }

    public function test_user_can_import_products(): void
    {
        $this->signIn();

        $this->performImportTest();
    }

    public function test_user_can_import_products_with_custom_fields(): void
    {
        $this->signIn();

        $this->performImportWithCustomFieldsTest();
    }

    public function test_it_finds_duplicate_products_during_import_via_name(): void
    {
        $this->signIn();
        $this->factory()->create(['name' => 'Duplicate Name']);

        $this->performImportWithDuplicateTest(['name' => 'Duplicate Name']);
    }

    public function test_it_finds_duplicate_products_during_import_via_sku(): void
    {
        $this->signIn();
        $this->factory()->create(['sku' => '001']);

        $this->performImportWithDuplicateTest(['sku' => '001']);
    }

    public function test_it_restores_trashed_duplicate_product_during_import(): void
    {
        $this->signIn();

        $product = $this->factory()->create(['sku' => '001']);

        $product->delete();

        $import = $this->performImportUpload($this->createFakeImportFile(
            [$this->createImportHeader(), $this->createImportRow(['sku' => '001'])]
        ));

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertFalse($product->fresh()->trashed());
    }

    public function test_user_can_load_the_products_table(): void
    {
        $this->performTestTableLoad();
    }

    public function test_products_table_loads_all_fields(): void
    {
        $this->performTestTableCanLoadWithAllFields();
    }

    public function test_user_can_force_delete_resource_record(): void
    {
        $this->signIn();

        $record = tap($this->factory()->create())->delete();

        $this->deleteJson($this->forceDeleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 0);
    }

    public function test_user_can_soft_delete_resource_record(): void
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
        $this->assertDatabaseCount($this->tableName(), 1);
    }

    public function test_product_can_be_viewed_without_own_permissions(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user, 'creator')->create();

        $this->getJson($this->showEndpoint($record))->assertOk()->assertJson(['id' => $record->id]);
    }

    public function test_edit_all_products_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('edit all products')->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload)->assertOk();
    }

    public function test_edit_own_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit own products')->signIn();
        $record1 = $this->factory()->for($user, 'creator')->create();
        $record2 = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record1), $this->samplePayload)->assertOk();
        $this->putJson($this->updateEndpoint($record2), $this->samplePayload)->assertForbidden();
    }

    public function test_edit_team_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('edit team products')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser, 'creator')->create();

        $this->putJson($this->updateEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_update_product(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->putJson($this->updateEndpoint($record), $this->samplePayload)->assertForbidden();
    }

    public function test_view_all_products_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('view all products')->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_view_team_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view team products')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record = $this->factory()->for($teamUser, 'creator')->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_view_own_product(): void
    {
        $user = $this->asRegularUser()->signIn();
        $record = $this->factory()->for($user, 'creator')->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_unauthorized_user_cannot_view_product(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_delete_any_product_permission(): void
    {
        $this->asRegularUser()->withPermissionsTo('delete any product')->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function test_delete_own_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete own products')->signIn();

        $record1 = $this->factory()->for($user, 'creator')->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_delete_team_products_permission(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('delete team products')->signIn();
        $teamUser = User::factory()->has(Team::factory()->for($user, 'manager'))->create();

        $record1 = $this->factory()->for($teamUser, 'creator')->create();
        $record2 = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record1))->assertNoContent();
        $this->deleteJson($this->deleteEndpoint($record2))->assertForbidden();
    }

    public function test_unauthorized_user_cannot_delete_product(): void
    {
        $this->asRegularUser()->signIn();
        $record = $this->factory()->create();

        $this->deleteJson($this->showEndpoint($record))->assertForbidden();
    }

    public function test_it_empties_products_trash(): void
    {
        $this->signIn();

        $this->factory()->count(2)->trashed()->create();

        $this->deleteJson('/api/trashed/products?limit=2')->assertJson(['deleted' => 2]);
        $this->assertDatabaseEmpty('products');
    }

    public function test_it_excludes_unauthorized_records_from_empty_products_trash(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own products', 'delete own products', 'bulk delete products'])->signIn();

        $this->factory()->trashed()->create();
        $this->factory()->trashed()->for($user, 'creator')->create();

        $this->deleteJson('/api/trashed/products')->assertJson(['deleted' => 1]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_it_does_not_empty_products_trash_if_delete_own_products_permission_is_not_applied(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo(['view own products', 'bulk delete products'])->signIn();

        $this->factory()->trashed()->for($user, 'creator')->create();

        $this->deleteJson('/api/trashed/products')->assertJson(['deleted' => 0]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_product_has_view_route(): void
    {
        $model = $this->factory()->create();

        $this->assertEquals('/products/'.$model->id, $this->resource()->viewRouteFor($model));
    }

    public function test_product_has_title(): void
    {
        $model = $this->factory()->make(['name' => 'Product Name']);

        $this->assertEquals('Product Name', $this->resource()->titleFor($model));
    }

    protected function assertResourceJsonStructure($response)
    {
        $response->assertJsonStructure([
            'actions', 'created_at', 'created_by', 'description', 'direct_cost', 'display_name', 'id', 'is_active', 'name', 'sku', 'tax_label', 'tax_rate', 'unit', 'unit_price', 'updated_at', 'was_recently_created', 'authorizations' => [
                'create', 'delete', 'update', 'view', 'viewAny',
            ],
        ]);
    }
}
