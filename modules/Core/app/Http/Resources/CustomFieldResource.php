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

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \Modules\Core\Models\CustomField */
class CustomFieldResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'field_type' => $this->field_type,
            'resource_name' => $this->resource_name,
            'field_id' => $this->field_id,
            'label' => $this->label,
            'options' => $this->when($this->options->isNotEmpty(), $this->options),
            'is_unique' => $this->is_unique,
        ], $request);
    }
}
