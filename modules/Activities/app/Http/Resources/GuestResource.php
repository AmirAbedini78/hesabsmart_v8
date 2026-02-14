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

namespace Modules\Activities\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Facades\Innoclapps;

/** @mixin \Modules\Activities\Models */
class GuestResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->guestable->getKey(),
            'guest_email' => $this->guestable->getGuestEmail(),
            'guest_display_name' => $this->guestable->getGuestDisplayName(),
            'resource_name' => Innoclapps::resourceByModel($this->guestable)->name(),
        ];
    }
}
