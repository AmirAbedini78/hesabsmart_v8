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

namespace Modules\MailClient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\MailClient\Models\EmailAccountFolder */
class EmailAccountFolderResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'email_account_id' => $this->email_account_id,
            'remote_id' => $this->remote_id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'syncable' => $this->syncable,
            'selectable' => $this->selectable,
            'unread_count' => (int) $this->unread_count ?: 0,
            'type' => $this->type,
        ];
    }
}
