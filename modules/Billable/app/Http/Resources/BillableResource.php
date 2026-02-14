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

namespace Modules\Billable\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\JsonResource;

/** @mixin \Modules\Billable\Models\Billable */
class BillableResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'tax_type' => $this->tax_type->name,
            'subtotal' => $this->subtotal()->getValue(),
            'total' => $this->total()->getValue(),
            'total_tax' => $this->totalTax()->getValue(),
            'taxes' => collect($this->taxes())->map(function ($tax) {
                $tax['total'] = $tax['total']->getValue();

                return $tax;
            }),
            'has_discount' => $this->hasDiscount(),
            'total_discount' => $this->discountedAmount()->getValue(),
            // 'terms'    => $this->terms,
            // 'notes'    => $this->notes,
            'products' => BillableProductResource::collection(
                $this->whenLoaded('products', fn () => $this->products, [])
            ),
        ], $request);
    }
}
