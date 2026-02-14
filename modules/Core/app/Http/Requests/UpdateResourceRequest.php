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

namespace Modules\Core\Http\Requests;

use Modules\Core\Fields\FieldsCollection;

class UpdateResourceRequest extends ResourceRequest
{
    use InteractsWithResourceFields;

    /**
     * Get the fields for the current request.
     */
    public function fields(): FieldsCollection
    {
        $this->resource()->for($this->record());

        return $this->resource()->fieldsForUpdate()->withoutReadonly();
    }
}
