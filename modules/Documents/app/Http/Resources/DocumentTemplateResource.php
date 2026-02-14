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

namespace Modules\Documents\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Resource\JsonResource;

/** @mixin \Modules\Documents\Models\DocumentTemplate */
class DocumentTemplateResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => clean($this->content),
            'view_type' => $this->view_type,
            'is_shared' => $this->is_shared,
            'user_id' => $this->user_id,
            'google_fonts' => $this->usedGoogleFonts(),
        ];
    }
}
