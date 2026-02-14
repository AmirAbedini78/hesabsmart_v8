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

namespace Modules\Core\Fields;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Core\Http\Resources\CountryResource;
use Modules\Core\Models\Country as CountryModel;

class Country extends BelongsTo
{
    /**
     * Create new Country instance.
     *
     * @param  null|string  $label
     */
    public function __construct($label = null)
    {
        parent::__construct('country', CountryModel::class, $label ?? __('core::country.country'));

        $this->acceptLabelAsValue(false)
            ->setJsonResource(CountryResource::class);
    }

    /**
     * Get option by given label.
     *
     * @param  string  $label
     * @return mixed
     */
    public function optionByLabel($label, ?Collection $options = null)
    {
        // Case sensitive comparision
        return ($options ?? $this->getCachedOptions())->first(function ($country) use ($label) {
            return Str::is($country->name, $label) ||
                Str::is($country->iso_3166_2, $label) ||
                Str::is($country->iso_3166_3, $label);
        });
    }

    /**
     * Get cached options collection.
     *
     * When importing data, the label as value function will be called
     * multiple times, we don't want all the queries executed multiple times
     * from the fields which are providing options via model.
     */
    public function getCachedOptions(): Collection
    {
        return $this->cachedOptions ??= CountryModel::get();
    }
}
