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

namespace Modules\Calls\Http\Resources;

use Illuminate\Http\Request;
use Modules\Comments\Http\Resources\CommentResource;
use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Http\Resources\ContactResource;
use Modules\Core\Http\Resources\MediaResource;
use Modules\Core\Resource\JsonResource;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Users\Http\Resources\UserResource;

/** @mixin \Modules\Calls\Models\Call */
class CallResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'body' => clean($this->body),
            'date' => $this->date,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'deals' => DealResource::collection($this->whenLoaded('deals')),
            $this->mergeWhen(! $request->isZapier(), [
                'comments' => CommentResource::collection($this->whenLoaded('comments')),
                'comments_count' => (int) $this->comments_count ?: 0,
                // Not used by the front-end, API can upload and use media in calls by providing with=media in URL parameter
                'media' => MediaResource::collection($this->whenLoaded('media')),
            ]),
        ], $request);
    }
}
