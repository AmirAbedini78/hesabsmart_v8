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

namespace Modules\Documents\Filters;

use Modules\Core\Filters\MultiSelect;
use Modules\Documents\Models\DocumentType;

class DocumentTypeFilter extends MultiSelect
{
    /**
     * Initialize new DocumentTypeFilter instance.
     */
    public function __construct()
    {
        parent::__construct('document_type_id', __('documents::fields.documents.type.name'));

        $this->labelKey('name')
            ->valueKey('id')
            ->options(
                fn () => DocumentType::select(['id', 'name', 'swatch_color'])
                    ->visible()
                    ->orderBy('name')
                    ->get()
            );
    }
}
