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

/** @mixin \Modules\Core\Models\MailableTemplate */
class MailableTemplateResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            'locale' => $this->locale,
            'subject' => $this->getSubject(),
            'html_template' => clean($this->getHtmlTemplate()),
            'text_template' => clean($this->getTextTemplate()),
            'placeholders' => $this->getPlaceholders(),
        ], $request);
    }
}
