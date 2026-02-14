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

namespace Modules\Brands\Services;

use Modules\Brands\Models\Brand;

class BrandService
{
    /**
     * Save new brand in storage.
     */
    public function create(array $attributes): Brand
    {
        $brand = Brand::create($attributes);

        $brand->saveVisibilityGroup($attributes['visibility_group'] ?? []);

        if ($brand->is_default === true) {
            $this->ensureNoOtherBrandIsDefaultThan($brand);
        }

        return $brand;
    }

    /**
     * Update the given brand in storage.
     */
    public function update(array $attributes, Brand $brand): Brand
    {
        $brand->fill($attributes)->save();

        if ($attributes['visibility_group'] ?? false) {
            $brand->saveVisibilityGroup($attributes['visibility_group']);
        }

        if ($brand->wasChanged('is_default') && $brand->is_default === true) {
            $this->ensureNoOtherBrandIsDefaultThan($brand);
        }

        return $brand;
    }

    /**
     * Ensure that no other brand is default than the given brand.
     */
    protected function ensureNoOtherBrandIsDefaultThan(Brand $brand): void
    {
        Brand::where('id', '!=', $brand->id)->update(['is_default' => false]);
    }
}
