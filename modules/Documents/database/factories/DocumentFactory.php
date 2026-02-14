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
use Modules\Brands\Models\Brand;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Models\DocumentType;
use Modules\Users\Models\User;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = \Modules\Documents\Models\Document::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(100),
            'document_type_id' => DocumentType::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraph(),
            'brand_id' => Brand::factory(),
            'created_by' => User::factory(),
            'owner_assigned_date' => now(),
            'requires_signature' => false,
            'status' => DocumentStatus::DRAFT->value,
            'locale' => 'en',
            'data' => [],
        ];
    }

    /**
     * Add the documents recipients.
     */
    public function hasRecipients(array $recipients): Factory
    {
        return $this->state(function (array $attributes) use ($recipients) {
            return [
                'data' => array_merge($attributes['data'], ['recipients' => $recipients]),
            ];
        });
    }

    /**
     * Indicate that the document requires signature.
     */
    public function mailable(int $userId): Factory
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'data' => array_merge($attributes['data'], ['send_initiated_by' => $userId]),
            ];
        });
    }

    /**
     * Indicate that the document requires signature.
     */
    public function signable(): Factory
    {
        return $this->state(function () {
            return [
                'requires_signature' => true,
            ];
        });
    }

    /**
     * Indicate that the document is with status draft.
     */
    public function draft(): Factory
    {
        return $this->state(function () {
            return [
                'status' => DocumentStatus::DRAFT->value,
            ];
        });
    }

    /**
     * Indicate that the document is with status sent.
     */
    public function sent(): Factory
    {
        return $this->state(function () {
            return [
                'status' => DocumentStatus::SENT->value,
                'original_date_sent' => $sentAt = now(),
                'last_date_sent' => $sentAt,
            ];
        });
    }

    /**
     * Indicate that the document is with status accepted.
     */
    public function accepted(): Factory
    {
        return $this->state(function () {
            return [
                'status' => DocumentStatus::ACCEPTED->value,
                'accepted_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the document is with status lost.
     */
    public function lost(): Factory
    {
        return $this->state(function () {
            return [
                'status' => DocumentStatus::LOST->value,
            ];
        });
    }
}
