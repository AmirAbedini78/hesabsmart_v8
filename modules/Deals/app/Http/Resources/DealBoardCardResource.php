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
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Resources\TagResource;
use Modules\Core\Support\GateHelper;

class DealBoardCardResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return with([
            'id' => $this->id,
            'name' => $this->name, // for activity create modal
            'amount' => $this->amount ?? 0,
            'display_name' => $this->name,
            'path' => $this->resource()->viewRouteFor($this->resource),
            'status' => $this->status->name,
            'authorizations' => GateHelper::authorizations($this->resource),
            'expected_close_date' => $this->expected_close_date,
            'next_activity_date' => $this->next_activity_date,
            'tags' => TagResource::collection($this->tags),
            'incomplete_activities_for_user_count' => (int) $this->incomplete_activities_for_user_count,
            'products_count' => (int) $this->products_count,
            'user_id' => $this->user_id,
            'swatch_color' => $this->swatch_color,
            'stage_id' => $this->stage_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ], function ($attributes) {
            if (! is_null($this->expected_close_date)) {
                $attributes['falls_behind_expected_close_date'] = $this->fallsBehindExpectedCloseDate;
            }

            return $attributes;
        });
    }
}
