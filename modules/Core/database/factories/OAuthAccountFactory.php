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
use Illuminate\Support\Str;
use Modules\Core\Models\OAuthAccount;
use Modules\Users\Models\User;

class OAuthAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = OAuthAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'google',
            'user_id' => User::factory(),
            'oauth_user_id' => Str::uuid()->__toString(),
            'email' => $this->faker->unique()->safeEmail(),
            'access_token' => Str::uuid()->__toString(),
            'expires' => now()->addDay(5)->timestamp,
        ];
    }

    /**
     * Indicate that the account requires authentication
     */
    public function requiresAuth(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'requires_auth' => true,
            ];
        });
    }
}
