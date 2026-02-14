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
use Modules\Core\Http\Resources\JsonResource;
use Modules\Users\Http\Resources\UserResource;

/** @mixin \Modules\MailClient\Models\ScheduledEmail */
class ScheduledEmailResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'to' => $this->to,
            'type' => $this->type,
            'email_account_id' => $this->email_account_id,
            'scheduled_at' => $this->scheduled_at,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
            'fail_reason' => $this->fail_reason,
            'retry_after' => $this->retry_after,
            'user' => new UserResource($this->whenLoaded('user')),
        ], $request);
    }
}
