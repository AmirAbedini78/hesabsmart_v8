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

use Illuminate\Contracts\Support\Arrayable;

class FilterChildGroup implements Arrayable
{
    /**
     * Initialize new FilterChildGroup instance.
     *
     * @param  array<array-key, Filter|FilterChildGroup>  $rules
     */
    public function __construct(
        protected array $rules,
        protected bool $quick = false,
        protected string $condition = 'all'
    ) {}

    /**
     * Get an array representation of the filter child group.
     *
     * @return array<array-key, array>
     */
    public function toArray()
    {
        return [
            'condition' => $this->condition,
            'quick' => $this->quick,
            'children' => collect($this->rules)->map(function (Filter|FilterChildGroup|array $rule) {
                return is_array($rule) ? $rule : $rule->toArray();
            }),
        ];
    }
}
