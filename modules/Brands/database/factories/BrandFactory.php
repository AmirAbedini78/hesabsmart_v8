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

namespace Modules\Brands\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = \Modules\Brands\Models\Brand::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $displayName = $this->faker->company(),
            'display_name' => $displayName,
            'config' => [
                'primary_color' => $this->faker->hexColor(),
                'pdf' => [
                    'font' => 'Arial, sans-serif',
                    'size' => 'a4',
                    'orientation' => 'landscape',
                ],
                'signature' => [
                    'bound_text' => [
                        'en' => 'I Agree',
                    ],
                ],
                'document' => [
                    'mail_message' => [
                        'en' => 'Message',
                    ],
                ],
            ],
        ];
    }

    /**
     * Indicate that the brand is default.
     */
    public function default(): Factory
    {
        return $this->state(function () {
            return [
                'is_default' => true,
            ];
        });
    }
}
