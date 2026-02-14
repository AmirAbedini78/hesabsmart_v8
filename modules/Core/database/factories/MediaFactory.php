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
use Modules\Core\Models\Media;

class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = config('mediable.aggregate_types');

        $type = $this->faker->randomElement(array_keys($types));

        return [
            'disk' => 'local',
            'directory' => implode('/', $this->faker->words($this->faker->randomDigit())),
            'filename' => $this->faker->filePath(),
            'extension' => $this->faker->randomElement($types[$type]['extensions']),
            'mime_type' => $this->faker->randomElement($types[$type]['mime_types']),
            'aggregate_type' => $type,
            'size' => $this->faker->randomNumber(),
        ];
    }
}
