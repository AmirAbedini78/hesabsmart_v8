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

namespace Modules\Deals\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;

class PipelineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Pipeline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->catchPhrase(),
        ];
    }

    /**
     * Indicate that the pipeline is primary.
     */
    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'flag' => Pipeline::PRIMARY_FLAG,
            ];
        });
    }

    /**
     * Indicate that the pipeline has stages.
     */
    public function withStages(bool|array $stages = true, string $relationship = 'stages'): static
    {
        $stages = is_array($stages) ? $stages : $this->factoryStages();
        $count = count($stages);

        return $this->has(Stage::factory()->state(new Sequence(
            ...$stages,
        ))->count($count), $relationship);
    }

    /**
     * Get the factory default stages
     */
    protected function factoryStages(): array
    {
        return [
            [
                'name' => 'Qualified To Buy',
                'win_probability' => 20,
                'display_order' => 1,
            ],
            [
                'name' => 'Contact Made',
                'win_probability' => 40,
                'display_order' => 2,
            ],
            [
                'name' => 'Presentation Scheduled',
                'win_probability' => 60,
                'display_order' => 3,
            ],
            [
                'name' => 'Proposal Made',
                'win_probability' => 80,
                'display_order' => 4,
            ],
            [
                'name' => 'Appointment scheduled',
                'win_probability' => 100,
                'display_order' => 5,
            ],
        ];
    }
}
