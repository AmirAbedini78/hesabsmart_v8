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

use Exception;
use JsonSerializable;
use Modules\Core\Support\Makeable;

class Operand implements JsonSerializable
{
    use Makeable;

    /**
     * The rule instance the operand is related to.
     */
    public ?Filter $rule = null;

    /**
     * The key value key should be taken from.
     */
    public string $valueKey = 'value';

    /**
     * The key label key should be taken from.
     */
    public string $labelKey = 'label';

    /**
     * Initialize new Operand instance.
     */
    public function __construct(public mixed $value, public string $label) {}

    /**
     * Set the operand filter.
     */
    public function filter(string|Filter $rule): static
    {
        if (is_string($rule)) {
            $rule = $rule::make($this->value);
        }

        if ($rule instanceof OperandFilter || $rule instanceof HasFilter) {
            throw new Exception(sprintf('Cannot use %s filter in operands.', [$rule::class]));
        }

        $this->rule = $rule;

        return $this;
    }

    /**
     * Create an operand from the given filter.
     */
    public static function from(Filter $filter)
    {
        return (new static($filter->field, $filter->label))->filter($filter);
    }

    /**
     * Get the filter instance.
     */
    public function getFilter(): ?Filter
    {
        return $this->rule;
    }

    /**
     * Set custom key for value.
     */
    public function valueKey(string $key): static
    {
        $this->valueKey = $key;

        return $this;
    }

    /**
     * Set custom label key.
     */
    public function labelKey(string $key): static
    {
        $this->labelKey = $key;

        return $this;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'rule' => $this->rule,
        ];
    }
}
