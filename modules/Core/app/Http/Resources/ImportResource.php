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
use Modules\Users\Http\Resources\UserResource;

/** @mixin \Modules\Core\Models\Import */
class ImportResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'file_name' => $this->file_name,
            'skip_file_filename' => $this->skip_file_filename,
            'mappings' => $this->data['mappings'] ?? [],
            'resource_name' => $this->resource_name,
            'status' => $this->status,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'duplicates' => $this->duplicates,
            'progress' => $this->progress(),
            'fields' => $this->serializeFields(),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'next_batch' => $this->nextBatch(),
            'completed_at' => $this->completed_at,
            'revertable' => $this->isRevertable(),
        ], $request);
    }
}
