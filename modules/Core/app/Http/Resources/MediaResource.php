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

/** @mixin \Modules\Core\Models\Media */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'file_name' => $this->basename,
            'extension' => $this->extension,
            'size' => $this->size,
            'disk_path' => $this->getDiskPath(),
            'mime_type' => $this->mime_type,
            'aggregate_type' => $this->aggregate_type,

            'view_url' => $this->getViewUrl(),
            'view_path' => $this->viewPath(),

            'preview_url' => $this->getPreviewUrl(),
            'preview_path' => $this->previewPath(),

            'download_url' => $this->getDownloadUrl(),
            'download_path' => $this->downloadPath(),

            'pending_data' => $this->whenLoaded('pendingData'),

            'via_text_attribute' => $this->viaTextAttribute(),
        ], $request);
    }
}
