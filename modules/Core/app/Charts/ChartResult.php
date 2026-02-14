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

namespace Modules\Core\Charts;

use Closure;
use JsonSerializable;

class ChartResult implements JsonSerializable
{
    /**
     * Chart colors
     */
    protected array $colors = [];

    /**
     * Create a new partition result instance
     */
    public function __construct(public array $value) {}

    /**
     * Format the labels for the chart result
     */
    public function label(Closure $callback): static
    {
        $this->value = collect($this->value)->mapWithKeys(function ($value, $label) use ($callback) {
            return [$callback($label) => $value];
        })->all();

        return $this;
    }

    /**
     * Set the chart colors
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Set the result value.
     */
    public function value(array $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Prepare chart result for JSON serialization
     */
    public function jsonSerialize(): array
    {
        return collect($this->value)->map(function ($value, $label) {
            return array_filter([
                'label' => $label,
                'value' => $value,
                'color' => data_get($this->colors, $label),
            ], function ($value) {
                return ! is_null($value);
            });
        })->values()->all();
    }
}
