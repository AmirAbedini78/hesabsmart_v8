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

namespace Modules\Contacts\Filters;

use Modules\Contacts\Models\Source as SourceModel;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Filters\Select;

class SourceFilter extends Select
{
    /**
     * Initialize new SourceFilter instance.
     */
    public function __construct()
    {
        parent::__construct('source_id', __('contacts::fields.companies.source.name'));

        $this->valueKey('id')
            ->labelKey('name')
            ->options(
                Innoclapps::resourceByModel(SourceModel::class)
            );
    }
}
