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

namespace Modules\Core\Filters;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Country as CountryModel;

class Country extends Select
{
    /**
     * Initialize new Country filter.
     */
    public function __construct()
    {
        parent::__construct('country_id', __('core::filters.country'));

        $this->valueKey('id')->labelKey('name')->options($this->countries(...));
    }

    /**
     * Get the filter available countries.
     */
    public function countries(): Collection
    {
        return CountryModel::get(['id', 'name']);
    }
}
