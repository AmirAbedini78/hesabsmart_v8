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
use Modules\Core\Resource\JsonResource;

/** @mixin \Modules\Billable\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        // TODO, in future allow the resource to not be always required and use some
        // default resource like this one
        return $this->withCommonData([], $request);
    }
}
