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

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use JsonSerializable;
use LogicException;
use Modules\Core\Contracts\Filters\DisplaysInQuickFilter;
use Modules\Core\Support\Element;
use Modules\Core\Support\HasHelpText;

abstract class Filter extends Element implements Arrayable, JsonSerializable
{
    use HasHelpText;

    /**
     * Define builder rule custom component.
     */
    public $component = null;

    /**
     * Custom column to use for the query.
     */
    public string|Expression|null $column = null;

    /**
     * Filter operators.
     */
    public array $filterOperators = [];

    /**
     * Exclude operators.
     */
    public array $excludeOperators = [];

    /**
     * @var null|callable
     */
    public $tapCallback;

    /**
     * Indicates whether the filter is static.
     */
    public bool $static = false;

    /**
     * Indicates that this filter can be added only once in a group.
     */
    public bool $onlyOncePerGroup = false;

    /**
     * @var null|callable
     */
    public $callback;

    /**
     * Filter current operator.
     */
    protected ?string $operator = null;

    /**
     * Filter current value.
     */
    protected mixed $value = null;

    /**
     * The quick filter data.
     */
    protected ?array $quickFilter = null;

    /**
     * Custom display as text.
     */
    protected string|array|null $displayAs = null;

    /**
     * Initialize new Filter instance.
     */
    public function __construct(public string $field, public ?string $label = null, ?array $operators = null)
    {
        is_array($operators) ? $this->operators($operators) : $this->determineOperators();
    }

    /**
     * Filter type from available filter types developed for front end.
     */
    public function type(): ?string
    {
        return null;
    }

    /**
     * Get the filter query builder component.
     */
    public function component(): string
    {
        return $this->component ? $this->component : $this->type().'-rule';
    }

    /**
     * Set that the filter will be displayed inline as well.
     */
    public function inQuickFilter(bool $multiple = false, ?string $label = null): static
    {
        if (! $this instanceof DisplaysInQuickFilter) {
            throw new LogicException(sprintf(
                '%s must implements %s to be used in quick filter.',
                static::class,
                DisplaysInQuickFilter::class
            ));
        }

        $isMultiple = property_exists($this, 'allowMultipleOptionsInQuickFilter') ? $multiple : false;

        $this->quickFilter = [
            'label' => $label,
            'multiple' => $isMultiple,
            'options' => [],
            'operator' => $this->getQuickFilterOperator($isMultiple),
        ];

        return $this;
    }

    /**
     * Set the column to be used when applying the query.
     */
    public function column(string|Expression $column): static
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get the column to be used when applying the query.
     */
    public function getColumn(Builder $query): string|Expression
    {
        if ($this->column instanceof Expression) {
            return $this->column;
        }

        return $query->qualifyColumn($this->column ?? $this->field());
    }

    /**
     * Set custom operators.
     */
    public function operators(array $operators): static
    {
        $this->filterOperators = $operators;

        return $this;
    }

    /**
     * Exclude the null operators.
     */
    public function withoutNullOperators(): static
    {
        $this->withoutOperators(['is_null', 'is_not_null']);

        return $this;
    }

    /**
     * Exclude operators.
     */
    public function withoutOperators(string|array $operator): static
    {
        $this->excludeOperators = is_array($operator) ? $operator : func_get_args();

        return $this;
    }

    /**
     * Get the filter field.
     */
    public function field(): string
    {
        return $this->field;
    }

    /**
     * Get the filter label.
     */
    public function label(): ?string
    {
        return $this->label;
    }

    /**
     * Add custom query handler instead of using the query builder.
     */
    public function applyQueryUsing(callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Add query tap callback.
     */
    public function tapQuery(callable $callback): static
    {
        $this->tapCallback = $callback;

        return $this;
    }

    /**
     * Mark the filter as static
     */
    public function asStatic(): static
    {
        $this->static = true;
        $this->onlyOncePerGroup = true;
        $this->component = 'static-rule';

        return $this;
    }

    /**
     * Add display.
     */
    public function displayAs(string|array $value): static
    {
        $this->displayAs = $value;

        return $this;
    }

    /**
     * Determine whether the filter is static.
     */
    public function isStatic(): bool
    {
        return $this->static === true;
    }

    /**
     * Check whether the filter is optionable.
     */
    public function isOptionable(): bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the filter is multi optionable.
     */
    public function isMultiOptionable(): bool
    {
        return $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Set the filter current value.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the filter active value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Set the filter current operator.
     */
    public function setOperator(?string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get the filter current operator.
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * Set that the filter can be added only once per group.
     */
    public function onlyOncePerGroup(): static
    {
        $this->onlyOncePerGroup = true;

        return $this;
    }

    /**
     * Create rule able array from the filter.
     */
    public function toArray(): array
    {
        return $this->getBuilderData();
    }

    /**
     * Get the fillter operators.
     */
    protected function getOperators(): array
    {
        return array_values(
            array_diff(
                array_unique($this->filterOperators),
                $this->excludeOperators
            )
        );
    }

    /**
     * Get operators options.
     */
    protected function operatorsOptions(): array
    {
        $options = [];

        foreach ($this->getOperators() as $operator) {
            $method = Str::studly(str_replace('.', '_', $operator)).'OperatorOptions';

            if (method_exists($this, $method)) {
                $options[$operator] = $this->{$method}() ?: [];
            }
        }

        return $options;
    }

    /**
     * Auto determines the operators on initialize based on QueryBuilder.
     */
    private function determineOperators(): void
    {
        foreach (QueryBuilder::$operators as $operator => $data) {
            if (in_array($this->type(), $data['apply_to'])) {
                $this->filterOperators[] = $operator;
            }
        }
    }

    /**
     * Get the filter builder data.
     */
    public function getBuilderData(): array
    {
        $rule = [
            'type' => 'rule',
            'query' => [
                'type' => $this->type(),
                'rule' => $this->field(),
                'operator' => $this->getOperator(),
                'value' => $this->getValue(),
            ],
        ];

        if ($this instanceof OperandFilter) {
            $rule['operand'] = $this->operand;
        }

        return $rule;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'id' => $this->field(),
            'label' => $this->label(),
            'type' => $this->type(),
            'operators' => $this->getOperators(),
            'operatorsOptions' => $this->operatorsOptions(),
            'component' => $this->component(),
            'isStatic' => $this->isStatic(),
            'operands' => $this instanceof OperandFilter ? $this->getOperands() : [],
            'hasAuthorization' => $this->hasAuthorization(),
            'helpText' => $this->helpText,
            'displayAs' => $this->displayAs,
            'quickFilter' => is_array($this->quickFilter) ? array_merge(
                $this->quickFilter, ['options' => $this->getQuickFilterOptions()]
            ) : null,
            'onlyOncePerGroup' => $this->onlyOncePerGroup,
        ], $this->meta());
    }
}
