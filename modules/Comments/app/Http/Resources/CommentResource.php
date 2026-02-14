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

namespace Modules\Comments\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\JsonResource;
use Modules\Users\Http\Resources\UserResource;

/** @mixin \Modules\Comments\Models\Comment */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'body' => clean($this->body),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
        ], $request);
    }
}
