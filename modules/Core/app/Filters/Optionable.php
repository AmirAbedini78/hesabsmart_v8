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

use Modules\Core\Contracts\Filters\DisplaysInQuickFilter;
use Modules\Core\Support\HasOptions;

abstract class Optionable extends Filter implements DisplaysInQuickFilter
{
    use HasOptions;

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'options' => $this->resolveOptions(),
        ]);
    }

    /**
     * Get the options to be used in quick filter.
     */
    public function getQuickFilterOptions(): array
    {
        return collect($this->resolveOptions())->map(function ($option) {
            return [
                'value' => $option[$this->valueKey],
                'label' => $option[$this->labelKey],
                'swatch_color' => $option['swatch_color'] ?? null,
            ];
        })->all();
    }

    /**
     * Get the quick filter operator.
     */
    public function getQuickFilterOperator(bool $multiple): string
    {
        return $multiple ? 'in' : 'equal';
    }
}
