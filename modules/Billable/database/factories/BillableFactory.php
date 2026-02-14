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

namespace Modules\Billable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billable\Enums\TaxType;
use Modules\Billable\Models\Billable;
use Modules\Deals\Models\Deal;

class BillableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Billable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tax_type' => TaxType::random(),
        ];
    }

    /**
     * Indicates that the billable has billableable
     **/
    public function withBillableable(mixed $for = null): static
    {
        return $this->for($for ?? Deal::factory(), 'billableable');
    }

    /**
     * Indicate billable will be tax exclusive.
     */
    public function taxExclusive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::exclusive,
            ];
        });
    }

    /**
     * Indicate billable will be tax inclusive.
     */
    public function taxInclusive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::inclusive,
            ];
        });
    }

    /**
     * Indicate billable will have no tax.
     */
    public function noTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::no_tax,
            ];
        });
    }
}
