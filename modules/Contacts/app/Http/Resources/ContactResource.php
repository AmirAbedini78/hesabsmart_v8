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

namespace Modules\Contacts\Http\Resources;

use Illuminate\Http\Request;
use Modules\Activities\Http\Resources\ActivityResource;
use Modules\Calls\Http\Resources\CallResource;
use Modules\Core\Http\Resources\ChangelogResource;
use Modules\Core\Http\Resources\MediaResource;
use Modules\Core\Resource\JsonResource;
use Modules\MailClient\Http\Resources\EmailAccountMessageResource;
use Modules\Notes\Http\Resources\NoteResource;

/** @mixin \Modules\Contacts\Models\Contact */
class ContactResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        ChangelogResource::topLevelResource($this->resource);

        return $this->withCommonData([
            'notes_count' => $this->whenCounted('notes', fn () => (int) $this->notes_count),
            'calls_count' => $this->whenCounted('calls', fn () => (int) $this->calls_count),
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'uploaded_avatar_url' => $this->uploaded_avatar_url,
            $this->mergeWhen(! $request->isZapier(), [
                'guest_email' => $this->getGuestEmail(),
                'guest_display_name' => $this->getGuestDisplayName(),
            ]),
            $this->mergeWhen(! $request->isZapier() && $this->userCanViewCurrentResource(), [
                'changelog' => ChangelogResource::collection($this->whenLoaded('changelog')),
                'notes' => NoteResource::collection($this->whenLoaded('notes')),
                'calls' => CallResource::collection($this->whenLoaded('calls')),
                'activities' => ActivityResource::collection($this->whenLoaded('activities')),
                'media' => MediaResource::collection($this->whenLoaded('media')),
                'emails' => EmailAccountMessageResource::collection($this->whenLoaded('emails')),
            ]),
        ], $request);
    }
}
