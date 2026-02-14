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

use Modules\Brands\Models\Brand;
use Modules\Core\Filters\MultiSelect;

class DocumentBrandFilter extends MultiSelect
{
    /**
     * Create new DocumentBrandFilter instance
     */
    public function __construct()
    {
        parent::__construct('brand_id', __('documents::fields.documents.brand.name'));

        $this->labelKey('name')
            ->valueKey('id')
            ->options(
                fn () => Brand::select(['id', 'name'])
                    ->visible()
                    ->orderBy('is_default', 'desc')
                    ->orderBy('name')
                    ->get()
            );
    }
}
