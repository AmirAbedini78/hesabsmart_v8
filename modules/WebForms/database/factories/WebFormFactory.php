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

namespace Modules\WebForms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Deals\Models\Pipeline;
use Modules\Users\Models\User;
use Modules\WebForms\Enums\WebFormSection;
use Modules\WebForms\Models\WebForm;

class WebFormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WebForm::class;

    /**
     * Submit data to merge
     */
    protected array $mergeSubmitData = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pipeline = Pipeline::factory()->withStages()->create();

        return [
            'title' => 'Web Form',
            'status' => 'active',
            'locale' => 'en',
            'user_id' => User::factory(),
            'styles' => [
                'primary_color' => $this->faker->hexColor(),
                'background_color' => $this->faker->hexColor(),
            ],
            'notifications' => [$this->faker->safeEmail()],
            'submit_data' => array_merge([
                'pipeline_id' => $pipeline->id,
                'stage_id' => $pipeline->stages()->first()->id,
                'action' => 'message',
                'success_title' => 'Form submitted.',
            ], $this->mergeSubmitData),
            'sections' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the web form has introduction section.
     */
    public function withIntroductionSection($merge = []): static
    {
        return $this->state(function (array $attributes) use ($merge) {
            $attributes['sections'][] = array_merge([
                'title' => 'Introduction Title',
                'message' => 'Introduction Message',
            ], $merge, ['type' => WebFormSection::INTRODUCTION]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Indicate that the web form has message section.
     */
    public function withMessageSection($message = null): static
    {
        return $this->state(function (array $attributes) use ($message) {
            $attributes['sections'][] = [
                'message' => $message ?? 'Message',
                'type' => WebFormSection::MESSAGE,
            ];

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Add new field section to the web form
     */
    public function addFieldSection($fieldAttribute, $resource = 'contacts', $merge = []): static
    {
        return $this->state(function (array $attributes) use ($merge, $fieldAttribute, $resource) {
            $attributes['sections'][] = array_merge([
                'label' => 'Field '.$fieldAttribute.' Label',
                'attribute' => $fieldAttribute,
                'isRequired' => false,
                'requestAttribute' => Str::random(),
                'resourceName' => $resource,
            ], $merge, ['type' => WebFormSection::FIELD]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Add new file section to the web form.
     */
    public function addFileSection($resource = 'contacts', $merge = []): static
    {
        return $this->state(function (array $attributes) use ($merge, $resource) {
            $attributes['sections'][] = array_merge([
                'label' => 'Attachment',
                'isRequired' => false,
                'multiple' => false,
                'requestAttribute' => Str::random(),
                'resourceName' => $resource,
            ], $merge, ['type' => WebFormSection::FILE]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Indicate that the web form has submit button section.
     */
    public function withSubmitButtonSection(array $merge = []): static
    {
        return $this->state(function (array $attributes) use ($merge) {
            $attributes['sections'][] = array_merge(['text' => 'Submit'], $merge, ['type' => WebFormSection::SUBMIT]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Merge the given submit data.
     */
    public function mergeSubmitData(array $data): static
    {
        $this->mergeSubmitData = $data;

        return $this;
    }

    /**
     * Indicate that the web form is inactive.
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }
}
