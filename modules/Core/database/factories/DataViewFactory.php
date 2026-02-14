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

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\DataView;
use Modules\Users\Models\User;

class DataViewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = DataView::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identifier' => 'users',
            'name' => 'View Name',
            'is_shared' => false,
            'user_id' => User::factory(),
            'rules' => [
                [
                    'condition' => 'all',
                    'children' => [[
                        'type' => 'rule',
                        'query' => [
                            'type' => 'text',
                            'opereator' => 'equal',
                            'rule' => 'test_attribute',
                            'operand' => 'Test',
                            'value' => 'Test',
                        ],
                    ]],
                ],
            ],
        ];
    }

    /**
     * Indicate that the view is system default.
     */
    public function default(string $flag = 'default-view'): static
    {
        return $this->state(function (array $attributes) use ($flag) {
            return [
                'flag' => $flag,
                'user_id' => null,
            ];
        });
    }

    /**
     * Indicate that the view is shared.
     */
    public function shared(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_shared' => true,
            ];
        });
    }
}
