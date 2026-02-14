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

namespace Modules\Calls\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Calls\Models\Call;
use Modules\Calls\Models\CallOutcome;
use Modules\Users\Models\User;

class CallFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Call::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => $this->faker->paragraph(),
            'date' => $this->faker->dateTimeBetween('-6 months')->format('Y-m-d H:i').':00',
            'call_outcome_id' => CallOutcome::factory(),
            'user_id' => User::factory(),
        ];
    }
}
