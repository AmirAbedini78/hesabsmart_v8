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

use Modules\Core\Facades\Fields;
use Modules\Core\Fields\Text;
use Tests\TestCase;

class FieldSettingsControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_fields_endpoints(): void
    {
        $this->getJson('/api/fields/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->postJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->getJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->deleteJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW/reset')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_fields_endpoints(): void
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertForbidden();
        $this->getJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertForbidden();
        $this->deleteJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW/reset')->assertForbidden();
    }

    public function test_fields_can_be_saved(): void
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        $this->postJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW, $attributes = [
            'test_field_1' => ['order' => 1],
            'test_field_2' => ['order' => 2],
        ])->assertNoContent();

        $this->assertEquals(
            $attributes['test_field_1']['order'],
            Fields::customized('testing', Fields::CREATE_VIEW)['test_field_1']['order']
        );

        $this->assertEquals(
            $attributes['test_field_2']['order'],
            Fields::customized('testing', Fields::CREATE_VIEW)['test_field_2']['order']
        );
    }

    public function test_fields_can_be_resetted(): void
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        $this->postJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW, [
            'test_field_1' => ['order' => 1],
            'test_field_2' => ['order' => 2],
        ]);

        $this->deleteJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW.'/reset')->assertOk();

        $this->assertCount(0, Fields::customized('testing', Fields::CREATE_VIEW));
    }

    public function test_cache_customized_fields_cache_is_cleared_after_update(): void
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        Fields::customize([
            'test_field_1' => ['order' => 2],
            'test_field_2' => ['order' => 1],
        ], 'testing', Fields::CREATE_VIEW);

        $fieldsNow = Fields::get('testing', Fields::CREATE_VIEW);

        $this->assertEquals(1, $fieldsNow[0]->order);
        $this->assertEquals('test_field_2', $fieldsNow[0]->attribute);
    }
}
