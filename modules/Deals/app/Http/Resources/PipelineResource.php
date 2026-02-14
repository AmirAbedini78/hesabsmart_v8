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

namespace Modules\Deals\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Resource\JsonResource;

/** @mixin \Modules\Deals\Models\Pipeline */
class PipelineResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            $this->mergeWhen(! $request->isZapier(), [
                'visibility_group' => $this->visibilityGroupData(),
                'flag' => $this->flag,
                'stages' => StageResource::collection($this->whenLoaded('stages')),
            ]),
        ], $request);
    }
}
