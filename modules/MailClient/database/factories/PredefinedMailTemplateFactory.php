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

namespace Modules\MailClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MailClient\Models\PredefinedMailTemplate;
use Modules\Users\Models\User;

class PredefinedMailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = PredefinedMailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->text(100),
            'subject' => $this->faker->text(100),
            'body' => '<p>'.$this->faker->paragraph().'</p',
            'is_shared' => true,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the template is personal.
     */
    public function personal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_shared' => false,
            ];
        });
    }

    /**
     * Indicate that the template is shared.
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
