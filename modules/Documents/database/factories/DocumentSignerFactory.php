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
use Modules\Documents\Models\Document;

class DocumentSignerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = \Modules\Documents\Models\DocumentSigner::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'document_id' => Document::factory(),
            'send_email' => false,
        ];
    }

    /**
     * Indicate that an email will be sent for this signer.
     */
    public function mailable(): Factory
    {
        return $this->state(function () {
            return [
                'send_email' => true,
            ];
        });
    }

    /**
     * Indicate that the signer has signed the document.
     */
    public function signed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'signature' => $attributes['name'],
                'signed_at' => now(),
                'sign_ip' => $this->faker->ipv4(),
            ];
        });
    }
}
