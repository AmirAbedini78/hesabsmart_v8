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
use Modules\Billable\Http\Resources\BillableResource;
use Modules\Brands\Http\Resources\BrandResource;
use Modules\Contacts\Http\Resources\CompanyResource;
use Modules\Contacts\Http\Resources\ContactResource;
use Modules\Core\Http\Resources\ChangelogResource;
use Modules\Core\Resource\JsonResource;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Users\Http\Resources\UserResource;

/** @mixin \Modules\Documents\Models\Document */
class DocumentResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'title' => $this->title,
            'document_type_id' => $this->document_type_id,
            'type' => new DocumentTypeResource($this->whenLoaded('type')),
            'status' => $this->status->value,
            'amount' => is_null($this->amount) ? 0 : (float) $this->amount,
            'requires_signature' => $this->requires_signature,
            'content' => clean($this->content),
            'view_type' => $this->view_type,
            'brand_id' => $this->brand_id,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'send_at' => $this->send_at,
            'original_date_sent' => $this->original_date_sent,
            'last_date_sent' => $this->last_date_sent,
            'accepted_at' => $this->accepted_at,
            'marked_accepted_by' => $this->marked_accepted_by,
            'locale' => $this->locale,
            'created_by' => $this->created_by,

            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'owner_assigned_date' => $this->owner_assigned_date,

            'public_url' => $this->when($request->user()?->can('view', $this->resource), $this->publicUrl, true),
            'signers' => DocumentSignerResource::collection($this->whenLoaded('signers')),
            'recipients' => $this->data['recipients'] ?? [],
            'send_mail_account_id' => ($this->data['send_mail_account_id'] ?? null) ? (int) $this->data['send_mail_account_id'] : null,
            'send_mail_subject' => $this->data['send_mail_subject'] ?? null,
            'send_mail_body' => $this->data['send_mail_body'] ?? null,
            'pdf' => $this->data['pdf'] ?? new \stdClass,
            'google_fonts' => $this->content->usedGoogleFonts(),
            $this->mergeWhen(! $request->isZapier() && $this->userCanViewCurrentResource(), [
                'changelog' => ChangelogResource::collection($this->whenLoaded('changelog')),
                'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
                'companies' => CompanyResource::collection($this->whenLoaded('companies')),
                'deals' => DealResource::collection($this->whenLoaded('deals')),
                'billable' => new BillableResource($this->whenLoaded('billable')),
            ]),
        ], $request);
    }
}
