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

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\JsonResource;

/** @mixin \Modules\Users\Models\Team */
class TeamResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            $this->mergeWhen($request->user()->isSuperAdmin(), [
                'description' => $this->description,
            ]),
            'user_id' => $this->user_id,
            'manager' => new UserResource($this->whenLoaded('manager')),
            'members' => UserResource::collection($this->whenLoaded('users')),
        ], $request);
    }
}
