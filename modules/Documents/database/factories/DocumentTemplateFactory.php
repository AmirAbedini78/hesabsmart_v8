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

namespace Modules\Documents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Users\Models\User;

class DocumentTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = \Modules\Documents\Models\DocumentTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->text(100),
            'content' => $this->faker->paragraph(),
            'is_shared' => false,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the template is shared.
     */
    public function shared(): Factory
    {
        return $this->state(function () {
            return [
                'is_shared' => true,
            ];
        });
    }
}
