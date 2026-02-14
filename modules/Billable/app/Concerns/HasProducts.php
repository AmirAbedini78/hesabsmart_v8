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

namespace Modules\Billable\Concerns;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Billable\Models\Billable;

/** @mixin \Modules\Core\Models\Model */
trait HasProducts
{
    /**
     * Provide the total column to be updated whenever the billable is updated
     */
    public function totalColumn(): ?string
    {
        return null;
    }

    /**
     * Check whether the model billable has products
     */
    public function hasProducts(): bool
    {
        if ($this->relationLoaded('products')) {
            return $this->products->isNotEmpty();
        }

        if ($this->relationLoaded('billable') && $this->billable->relationLoaded('products')) {
            return $this->billable->products->isNotEmpty();
        }

        return $this->products()->count() > 0;
    }

    /**
     * Get the deal billable model
     */
    public function billable(): MorphOne
    {
        return $this->morphOne(Billable::class, 'billableable')->withDefault(array_filter([
            'tax_type' => Billable::defaultTaxType(),
        ]));
    }

    /**
     * Get all of the products for the model.
     */
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            \Modules\Billable\Models\BillableProduct::class,
            \Modules\Billable\Models\Billable::class,
            'billableable_id',
            'billable_id',
            'id',
            'id'
        )->where('billableable_type', $this::class);
    }
}
