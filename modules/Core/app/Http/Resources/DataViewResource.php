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

/** @mixin \Modules\Core\Models\DataView */
class DataViewResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            'identifier' => $this->identifier,
            'rules' => $this->rules,
            'config' => $this->config,
            'user_id' => $this->user_id,
            'is_shared' => $this->is_shared,
            'is_shared_from_another_user' => $this->isSharedFromAnotherUser($request),
            'is_system_default' => $this->isSystemDefault(),
            'is_open_for_user' => $this->isOpenFor($request->user()),
            'user_order' => $this->getOrderFor($request->user()),
        ], $request);
    }
}
