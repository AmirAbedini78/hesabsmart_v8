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

namespace Modules\Brands\Database\State;

use Modules\Brands\Models\Brand;

class EnsureDefaultBrandIsPresent
{
    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        Brand::create([
            'name' => config('app.name'),
            'display_name' => config('app.name'),
            'is_default' => true,
            'config' => [
                'primary_color' => '#4f46e5',
            ],
        ]);
    }

    private function present(): bool
    {
        return Brand::query()->where('is_default', true)->count() > 0;
    }
}
