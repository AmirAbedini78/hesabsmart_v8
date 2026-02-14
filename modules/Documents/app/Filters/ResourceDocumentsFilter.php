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

use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\Number;
use Modules\Core\Filters\Numeric;
use Modules\Core\Filters\Operand;
use Modules\Core\Filters\Text;

class ResourceDocumentsFilter extends HasFilter
{
    /**
     * Initialize new ResourceDocumentsFilter instance.
     */
    public function __construct()
    {
        parent::__construct('documents', __('documents::document.document'));

        $this->setOperands([
            Operand::from(Numeric::make('amount', __('documents::fields.documents.amount'))),
            Operand::from(DocumentStatusFilter::make()),
            Operand::from(DocumentTypeFilter::make()),
            Operand::from(DocumentBrandFilter::make()),
            Operand::from(Text::make('name', __('documents::document.title'))),
            Operand::from(Number::make('total_count', __('documents::document.total_documents'))->countFromRelation('documents')),
        ]);
    }
}
