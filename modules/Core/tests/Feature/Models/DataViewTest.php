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

namespace Modules\Core\Tests\Feature\Models;

use Illuminate\Support\Facades\Lang;
use Modules\Core\Models\DataView;
use Modules\Users\Models\User;
use Tests\TestCase;

class DataViewTest extends TestCase
{
    public function test_view_has_user(): void
    {
        $view = DataView::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $view->user);
    }

    public function test_view_is_system_default_when_flag_is_not_empty(): void
    {
        $view = DataView::factory()->make(['flag' => 'flag']);

        $this->assertTrue($view->isSystemDefault());
    }

    public function test_view_is_not_system_default_when_flag_is_empty(): void
    {
        $view = DataView::factory()->create(['flag' => null]);

        $this->assertFalse($view->isSystemDefault());
    }

    public function test_view_name_can_be_custom_translated(): void
    {
        $view = DataView::factory()->create(['name' => 'View name']);

        Lang::addLines([
            'custom.view.'.$view->id => 'Custom view name',
        ], 'en');

        $this->assertEquals('Custom view name', $view->name);
    }

    public function test_it_returns_the_original_name_when_no_custom_translation_exists(): void
    {
        $view = DataView::factory()->make(['name' => 'View name']);

        $this->assertEquals('View name', $view->name);
    }

    public function test_view_has_rules(): void
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => 'text',
                'opereator' => 'equal',
                'rule' => 'test_attribute',
                'operand' => 'Test',
                'value' => 'Test',
            ],
        ];

        $view = DataView::factory()->make([
            'rules' => $groups = [
                [
                    'condition' => 'all',
                    'children' => [$rule],
                ],
            ],
        ]);

        $this->assertEquals($groups, $view->rules);
    }

    public function test_view_rules_can_be_set_only_by_passing_children(): void
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => 'text',
                'opereator' => 'equal',
                'rule' => 'test_attribute',
                'operand' => 'Test',
                'value' => 'Test',
            ],
        ];

        $view = DataView::factory()->create(['rules' => $rule]);

        $expected = [
            [
                'condition' => 'all',
                'children' => $rule,
            ],
        ];

        $this->assertEquals($expected, $view->rules);
    }

    public function test_view_rules_can_be_set_only_by_passing_group_children(): void
    {
        $rule = [
            [
                'type' => 'rule',
                'query' => [
                    'type' => 'text',
                    'opereator' => 'equal',
                    'rule' => 'test_attribute',
                    'operand' => 'Test',
                    'value' => 'Test',
                ],
            ],
        ];

        $view = DataView::factory()->create(['rules' => $rule]);

        $expected = [
            [
                'condition' => 'all',
                'children' => $rule,
            ],
        ];

        $this->assertEquals($expected, $view->rules);
    }

    public function test_view_can_be_translated_with_custom_group(): void
    {
        $model = DataView::factory()->create(['name' => 'Original']);

        Lang::addLines(['custom.view.'.$model->id => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_view_can_be_translated_with_lang_key(): void
    {
        $model = DataView::factory()->create(['name' => 'custom.view.some']);

        Lang::addLines(['custom.view.some' => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_it_uses_database_name_when_no_custom_trans_available(): void
    {
        $model = DataView::factory()->create(['name' => 'Database Name']);

        $this->assertSame('Database Name', $model->name);
    }
}
