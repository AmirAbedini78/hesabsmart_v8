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

/**
 *   USAGE:
 *   OperandFilter::make('revenue', 'Revenue')->setOperands([
 *       Operand::make('total_revenue', 'Total Revenue')->filter(NumericFilter::class),
 *       Operand::make('annual_revenue', 'Annual Revenue')->filter(NumericFilter::class),
 *   [),
 */
class OperandFilter extends Filter
{
    /**
     * The filter active operand.
     */
    protected ?string $operand = null;

    /**
     * Filter available operands.
     *
     * @var null|array|callable
     */
    protected $operands = null;

    /**
     * Set the filter active operand field/name.
     */
    public function setOperand(string $operand): static
    {
        $this->operand = $operand;

        return $this;
    }

    /**
     * Get the filter active operand field/name.
     */
    public function getOperand(): ?string
    {
        return $this->operand;
    }

    /**
     * Set the filter available operands.
     */
    public function setOperands(array|callable|null $operands): static
    {
        $this->operands = $operands;

        return $this;
    }

    /**
     * Get the filter available operands.
     */
    public function getOperands(): ?array
    {
        if (is_callable($this->operands)) {
            // Ensure they are not resolved every time this function is called.
            return $this->operands = call_user_func($this->operands);
        }

        return $this->operands;
    }

    /**
     * Find operand filter by given value (field/name).
     */
    public function findOperand($value): ?Operand
    {
        return collect($this->getOperands())->first(fn (Operand $operand) => $operand->value == $value);
    }

    /**
     * Get the active operand instance.
     */
    public function getOperandInstance(?string $value = null): ?Operand
    {
        return $this->findOperand($value ?: $this->operand);
    }

    /**
     * Indicates that the filter operands will be hidden on the front-end.
     * Useful when only 1 operand is used, which is by default pre-selected.
     */
    public function hideOperands(bool $bool = true): static
    {
        $this->withMeta([__FUNCTION__ => $bool]);

        return $this;
    }

    /**
     * Defines a filter type.
     */
    public function type(): string
    {
        return 'nullable';
    }
}
