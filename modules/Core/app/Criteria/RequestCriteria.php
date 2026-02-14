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

namespace Modules\Core\Criteria;

use BackedEnum;
use Exception;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Modules\Core\Concerns\HasDisplayOrder;
use Modules\Core\Contracts\Criteria\QueryCriteria;

class RequestCriteria implements QueryCriteria
{
    public const QUERY_KEY = 'q';

    public const SEARCH_FIELDS_KEY = 'search_fields';

    public const SEARCH_MATCH_KEY = 'search_match';

    public const ORDER_KEY = 'order';

    public const SELECT_KEY = 'select';

    public const WITH_KEY = 'with';

    public const TAKE_KEY = 'take';

    protected array $acceptedConditions = ['=', 'like', 'in'];

    protected array $searchFields = [];

    /**
     * Initialize new RequestCriteria class
     */
    public function __construct(protected ?HttpRequest $request = null)
    {
        $this->request = $request ?: Request::instance();
    }

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): Builder
    {
        $searchQuery = $this->request->get(static::QUERY_KEY, '');

        if ($searchQuery && count($this->searchFields)) {
            $performedSearch = false;
            $isFirstField = true;
            $searchMatch = $this->request->get(static::SEARCH_MATCH_KEY);
            $requestProvidedSearchFields = $this->request->str(static::SEARCH_FIELDS_KEY, '')->explode(';')->filter()->all();
            $forceAndWhere = $searchMatch && (strtolower($searchMatch) === 'and' || strtolower($searchMatch) === 'all');
            $valuesFromSearchQuery = $this->parseValuesFromSearchQuery($searchQuery);
            $searchQuery = count($valuesFromSearchQuery) === 0 ? $searchQuery : null;

            $fields = $this->parseSearchFields(
                array_merge($this->searchFields, [$query->getModel()->getKeyName()]),
                $requestProvidedSearchFields,
                $query->getModel()
            );

            $query = $query->where(function ($query) use (
                $fields,
                $forceAndWhere,
                $searchQuery,
                $valuesFromSearchQuery,
                &$isFirstField,
                &$performedSearch,
            ) {
                /** @var Builder $query */
                foreach ($fields as $fieldData) {
                    ['condition' => $condition, 'queryColumn' => $column] = $fieldData;
                    $relation = null;
                    $model = $query->getModel();

                    $value = $this->getFieldValue($fieldData, $searchQuery, $valuesFromSearchQuery, $model);

                    if (is_null($value) || $this->shouldSkipSearchField($fieldData, $value, $model)) {
                        continue;
                    }

                    // Auto-adjust condition based on search query value
                    $fieldData = $this->autoAdjustCondition($fieldData, $value);
                    $condition = $fieldData['condition'];

                    if (! $fieldData['isExpression'] && stripos($column, '.')) {
                        [$relation, $column] = explode('.', $column);
                    }

                    $boolean = $isFirstField || $forceAndWhere ? 'and' : 'or';

                    $queryValue = $this->getFieldValueForQuery($fieldData, $value, $model);

                    $this->applySearch($queryValue, $query, $column, $condition, $relation, $boolean);
                    $performedSearch = true;
                    $isFirstField = false;
                }
            });

            abort_if(! $performedSearch, 500, 'Invalid search fields.');
        }

        if ($take = $this->request->get(static::TAKE_KEY)) {
            $query = $query->take($take);
        }

        $query = $this->applyOrder($this->request->get(static::ORDER_KEY, []), $query);
        $query = $this->applySelect($this->request->get(static::SELECT_KEY), $query);
        $query = $this->applyWith($this->request->get(static::WITH_KEY), $query);

        return $query;
    }

    /**
     * Set the available search fields.
     */
    public function setSearchFields(array $fields)
    {
        $this->searchFields = $fields;

        return $this;
    }

    /**
     * Get field value intended to be used for building the query.
     */
    protected function getFieldValueForQuery(array $fieldData, mixed $value, Model $model)
    {
        ['fieldKey' => $fieldKey, 'condition' => $condition] = $fieldData;

        if ($this->isFieldBoolean($fieldKey, $model)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOL);
        }

        if (! is_null($value)) {
            if ($condition === 'like') {
                $value = "%{$value}%";
            } elseif ($condition === 'in') {
                $value = Str::of($value)->explode(',')
                    ->map(fn (string $value) => trim($value))
                    ->reject(fn (string $v) => $v === '')
                    ->map(fn (string $v) => $this->getValidEnumValue($model, $fieldKey, $v))
                    ->all();

                if (count($value) === 0) {
                    $value = null;
                }
            }
        }

        return $value;
    }

    /**
     * Get the field value for the request.
     */
    protected function getFieldValue(array $fieldData, ?string $searchQuery, array $valuesFromSearchQuery, Model $model)
    {
        ['fieldKey' => $fieldKey] = $fieldData;

        return $this->getValidEnumValue(
            $model,
            $fieldKey,
            $valuesFromSearchQuery[$fieldKey] ?? $searchQuery
        );
    }

    /**
     * Check if the field should be skipped from search.
     */
    protected function shouldSkipSearchField(array $fieldData, mixed $value, Model $model): bool
    {
        ['fieldKey' => $fieldKey, 'queryColumn' => $column, 'condition' => $condition] = $fieldData;

        if ($this->isFieldBoolean($fieldKey, $model)) {
            return ! in_array($value, ['on', 'off', '1', '0', 'true', 'false']);
        }

        // Don't skip if it's an 'in' condition with array values (comma-separated IDs)
        if ($condition === 'in' && is_array($value) || (is_string($value) && str_contains($value, ','))) {
            return false;
        }

        // For numeric fields, only skip if the value is non-numeric and it's not an 'in' condition
        if ($this->isFieldNumeric($fieldKey, $model)) {
            $isSearchQueryNumeric = is_numeric($value);

            if (! $isSearchQueryNumeric) {
                // When the "queryColumn" is different from the actual key, means that
                // there is different implementation for search available for this field, see custom field select/radio.
                return $column === $fieldKey;
            }
        }

        return false;
    }

    /**
     * Ensure enum casted field has proper value.
     */
    protected function getValidEnumValue($model, $field, $value)
    {
        if ($value && $model->hasCast($field)) {
            $cast = $model->getCasts()[$field];

            if (is_subclass_of($cast, BackedEnum::class)) {
                foreach ($cast::cases() as $case) {
                    if (strtolower($case->name) === strtolower($value)) {
                        return $case->value;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Apply search to the given query.
     */
    protected function applySearch(
        mixed $value,
        Builder $query,
        Expression|string $column,
        string $condition,
        ?string $relation,
        string $boolean = 'and'
    ): void {
        if (! is_null($relation)) {
            $callback = function (Builder $query) use ($column, $value, $condition) {
                if (in_array(HasDisplayOrder::class, class_uses_recursive($query->getModel()))) {
                    $query->withoutGlobalScope('displayOrder');
                }

                if ($condition === 'in') {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $condition, $value);
                }
            };

            $query->has($relation, '>=', 1, $boolean, $callback);
        } else {
            // Expressions must be manually qualified before passing.
            $column = $column instanceof Expression ? $column : $query->qualifyColumn($column);

            if ($condition === 'in') {
                $query->whereIn($column, $value, $boolean);
            } else {
                $query->where($column, $condition, $value, $boolean);
            }
        }
    }

    /**
     * Apply order for the current request.
     *
     * @param  mixed  $order
     */
    protected function applyOrder($order, Builder $query): Builder
    {
        // No order applied
        if (empty($order)) {
            return $query;
        }

        // Allowing passing sort option like order=created_at|desc
        if (! is_array($order)) {
            $orderArray = explode('|', $order);

            $order = [
                'field' => $orderArray[0],
                'direction' => $orderArray[1] ?? '',
            ];
        }

        // Is not multidimensional array, order by one field and direction
        // e.q. ['field'=>'fieldName', 'direction'=>'asc']
        if (isset($order['field'])) {
            $order = [$order];
        }

        $order = collect($order)->reject(function ($order) {
            return empty($order['field']);
        });

        // Remove any default order
        if ($order->isNotEmpty()) {
            $query = $query->reorder();
        }

        foreach ($order->map(fn ($order) => array_merge($order, [
            'direction' => ($order['direction'] ?? '') ?: 'asc',
        ])) as $order) {
            ['field' => $field, 'direction' => $direction] = $order;
            $split = explode('|', $field);

            if (count($split) > 1) {
                $this->orderByRelationship($split, $direction, $query);
            } else {
                $qualifiedColumnName = $this->isAggregateField($field) ? $field : $query->qualifyColumn($field);

                $query = $query->orderBy($qualifiedColumnName, $direction);
            }
        }

        return $query;
    }

    /**
     * Check if the field is aggregate.
     *
     * @see https://laravel.com/docs/10.x/eloquent-relationships#other-aggregate-functions
     */
    protected function isAggregateField(string $field): bool
    {
        $aggregates = ['_sum_', '_min_', '_max_', '_avg_', '_exists_'];

        return str_ends_with($field, '_count') || Str::contains($field, $aggregates);
    }

    /**
     * Order the query by relationship.
     *
     * @param  array  $orderData
     * @param  string  $dir
     * @return void
     */
    protected function orderByRelationship($orderData, $dir, Builder $model): Builder
    {
        /*
        * ex.
        * products|description -> join products on current_table.product_id = products.id order by description
        *
        * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
        * by products.description (in case both tables have same column name)
        */
        $table = $model->getModel()->getTable();
        $sortTable = $orderData[0];
        $sortColumn = $orderData[1];

        $orderData = explode(':', $sortTable);

        if (count($orderData) > 1) {
            $sortTable = $orderData[0];
            $keyName = $table.'.'.$orderData[1];
        } else {
            /*
             * If you do not define which column to use as a joining column on current table, it will
             * use a singular of a join table appended with _id
             *
             * ex.
             * products -> product_id
             */
            $prefix = Str::singular($sortTable);
            $keyName = $table.'.'.$prefix.'_id';
        }

        return $model->leftJoin($sortTable, $keyName, '=', $sortTable.'.id')
            ->orderBy($sortTable.'.'.$sortColumn, $dir)
            ->addSelect($table.'.*');
    }

    /**
     * Apply select fields to model.
     */
    protected function applySelect(string|array|null $select, Builder $query): Builder
    {
        if (! empty($select)) {
            $query = $query->select(is_string($select) ? explode(';', $select) : $select);
        }

        return $query;
    }

    /**
     * Apply with relationships to model.
     */
    protected function applyWith(string|array|null $with, Builder $query): Builder
    {
        if (! empty($with)) {
            $query = $query->with(is_string($with) ? explode(';', $with) : $with);
        }

        return $query;
    }

    /**
     * @param  string  $query
     */
    protected function parseValuesFromSearchQuery($query): array
    {
        $values = [];

        // Check if the search query is not an URL
        if (! filter_var($query, FILTER_VALIDATE_URL) &&
            ! Str::contains($query, ['http:', 'https:']) &&
            stripos($query, ':')
        ) {
            $fields = explode(';', $query);

            foreach ($fields as $row) {
                try {
                    [$field, $value] = explode(':', $row);
                    $values[$field] = $value;
                } catch (Exception) {
                    // Surround offset error
                }
            }
        }

        return $values;
    }

    /**
     * Check whether the given field is numeric.
     */
    protected function isFieldNumeric(string $field, Model $model)
    {
        if ($field === $model->getKeyName() && $model->incrementing) {
            return true;
        }

        return Str::contains(
            $model->getCasts()[$field] ?? '',
            ['real', 'int', 'float', 'double', 'decimal']
        );
    }

    /**
     * Check whether the given field is boolean.
     */
    protected function isFieldBoolean(string $field, Model $model): bool
    {
        return str_contains($model->getCasts()[$field] ?? '', 'bool');
    }

    /**
     * Converts fields to a common format for processing.
     *
     * @param  array  $fields  The fields to be converted.
     * @return array The fields in a standardized format.
     */
    protected function convertFieldsToCommonFormat(array $fields)
    {
        $parsed = [];

        foreach ($fields as $field => $condition) {
            $queryColumn = null;
            $allowedConditions = null;

            if (is_numeric($field)) {
                $queryColumn = $condition;
                $field = $condition;
                $condition = '=';
            } elseif (is_array($condition)) {
                $queryColumn = $condition['column'] ?? $field;
                $conditionValue = $condition['condition'] ?? '=';
                $allowedConditions = is_array($conditionValue) ? $conditionValue : [$conditionValue];
            } elseif ($condition instanceof Expression) {
                $queryColumn = $condition;
                $condition = '=';
            }

            // Set default allowed conditions if not already set
            if ($allowedConditions === null) {
                $allowedConditions = [is_string($condition) ? $condition : '='];
                // For non-ID fields that use 'like', also allow '=' condition
                if ($condition === 'like') {
                    $allowedConditions[] = '=';
                }
            }

            $parsed[$field] = [
                'fieldKey' => $field,
                'queryColumn' => $queryColumn = $queryColumn ?? $field,
                'condition' => strtolower($allowedConditions[0]), // Default to first condition
                'allowedConditions' => array_map('strtolower', $allowedConditions),
                'isExpression' => $queryColumn instanceof Expression,
            ];
        }

        return $parsed;
    }

    /**
     * Parses the searchable fields based on allowed and provided fields.
     *
     * @param  array  $allowed  Allowed searchable fields.
     * @param  array  $providedFields  Fields provided for searching.
     * @param  Model  $model  The model instance.
     * @return array Searchable fields after parsing.
     */
    protected function parseSearchFields(array $allowed, array $providedFields, Model $model): array
    {
        $allowed = $this->convertFieldsToCommonFormat($allowed);

        // Ensure ID field (primary key) always supports 'in' and '=' conditions
        $keyName = $model->getKeyName();
        if ($keyName && isset($allowed[$keyName])) {
            $currentConditions = $allowed[$keyName]['allowedConditions'];
            $defaultConditions = ['=', 'in'];
            $mergedConditions = array_unique(array_merge($currentConditions, $defaultConditions));

            $allowed[$keyName]['allowedConditions'] = $mergedConditions;

            // If current condition is not in the merged conditions, use the first default
            if (! in_array($allowed[$keyName]['condition'], $mergedConditions)) {
                $allowed[$keyName]['condition'] = $defaultConditions[0];
            }
        }

        if (count($providedFields) === 0) {
            return $allowed;
        }

        $whitelisted = $this->filterWhitelistedFields($allowed, $providedFields);

        abort_unless(count($whitelisted), 403, sprintf(
            'None of the search fields were accepted. Acceptable search fields are: %s',
            implode(',', array_keys($allowed))
        ));

        return $whitelisted;
    }

    /**
     * Filters whitelisted fields from the provided search fields.
     *
     * @param  array  $allowed  Allowed fields.
     * @param  array  $providedSearchFields  Provided search fields.
     * @return array Filtered whitelisted fields.
     */
    protected function filterWhitelistedFields(array $allowed, array $providedSearchFields): array
    {
        $whitelisted = [];

        foreach ($providedSearchFields as $fieldString) {
            // URL decode the field string first
            $fieldString = urldecode($fieldString);
            $parts = explode(':', $fieldString);
            $field = $parts[0];

            if (array_key_exists($field, $allowed)) {
                $condition = $parts[1] ?? null;

                // If condition is specified, validate it
                if ($condition) {
                    $conditionLower = strtolower($condition);

                    if (in_array($conditionLower, $this->acceptedConditions) &&
                        in_array($conditionLower, $allowed[$field]['allowedConditions'])) {
                        // Valid condition - add field with the specified condition
                        $whitelisted[$field] = $allowed[$field];
                        $whitelisted[$field]['condition'] = $conditionLower;
                    }
                    // If invalid condition, skip this field entirely
                } else {
                    // No condition specified - use default
                    $whitelisted[$field] = $allowed[$field];
                }
            }
        }

        return $whitelisted;
    }

    /**
     * Auto-adjust condition based on search query value.
     */
    protected function autoAdjustCondition(array $fieldData, mixed $value): array
    {
        // Only auto-adjust for ID field and when value contains comma
        if (is_string($value) && str_contains($value, ',') &&
            in_array('in', $fieldData['allowedConditions']) &&
            $fieldData['condition'] === '=' &&
            $fieldData['fieldKey'] === 'id') {
            $fieldData['condition'] = 'in';
        }

        return $fieldData;
    }
}
