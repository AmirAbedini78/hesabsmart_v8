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

use Illuminate\Support\Arr;
use Modules\Core\Rules\StringRule;

trait HasValidationRules
{
    /**
     * Validation rules.
     */
    public array $rules = [];

    /**
     * Validation creation rules.
     */
    public array $creationRules = [];

    /**
     * Validation import rules.
     */
    public array $importRules = [];

    /**
     * Validation update rules.
     */
    public array $updateRules = [];

    /**
     * Custom validation error messages.
     */
    public array $validationMessages = [];

    /**
     * Set field validation rules for all requests.
     */
    public function rules(mixed $rules): static
    {
        $this->rules = array_merge(
            $this->rules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules that are only applied on create request.
     */
    public function creationRules(mixed $rules): static
    {
        $this->creationRules = array_merge(
            $this->creationRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules that are only applied on update request.
     */
    public function updateRules(mixed $rules): static
    {
        $this->updateRules = array_merge(
            $this->updateRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules for import.
     */
    public function importRules(mixed $rules): static
    {
        $this->importRules = array_merge(
            $this->importRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for import.
     */
    public function getImportRules(): array
    {
        $rules = [
            $this->requestAttribute() => $this->importRules,
        ];

        // we will remove the array rule in case found
        // because the import can handle arrays via coma separated values
        // for specific fields, other fields must implement their own logic
        return $this->sortRules(collect(array_merge_recursive(
            $this->getRules(),
            $rules
        ))->mapWithKeys(function ($rules, $attribute) {
            return [$attribute => collect($rules)->reject(fn ($rule) => $rule === 'array')->values()->all()];
        })->all());
    }

    /**
     * Get field validation rules for the request.
     */
    public function getRules(): array
    {
        return $this->sortRules($this->createRulesArray($this->rules));
    }

    /**
     * Get the field validation rules for create request.
     */
    public function getCreationRules(): array
    {
        $rules = $this->createRulesArray($this->creationRules);

        return $this->sortRules(array_merge_recursive(
            $this->getRules(),
            $rules
        ));
    }

    /**
     * Get the field validation rules for update request.
     */
    public function getUpdateRules(): array
    {
        $rules = $this->createRulesArray($this->updateRules);

        return $this->sortRules(array_merge_recursive(
            $this->getRules(),
            $rules
        ));
    }

    /**
     * Sort the given rules in a proper format.
     */
    protected function sortRules(array $allRules)
    {
        $order = [
            'bail' => 1,
            'sometimes' => 2,
            'nullable' => 3,
            'required' => 3,
            StringRule::class => 5,
            // Default for any other rules
        ];

        foreach ($allRules as &$rules) {
            usort($rules, function ($a, $b) use ($order) {
                // Convert rule objects to class names or keep as string
                $aValue = is_object($a) ? get_class($a) : $a;
                $bValue = is_object($b) ? get_class($b) : $b;

                $aOrder = $order[$aValue] ?? 1000;
                $bOrder = $order[$bValue] ?? 1000;

                return $aOrder <=> $bOrder;
            });
        }

        return $allRules;
    }

    /**
     * Create rules ready array.
     */
    protected function createRulesArray(array $rules): array
    {
        // If the array is not list, probably the user added array validation
        // rules e.q. '*.key' => 'required', in this case, we will make sure to include them
        if (! array_is_list($rules)) {
            $groups = collect($rules)->mapToGroups(function ($rules, $wildcard) {
                // If the $wildcard is integer, this means that it's a rule for the main field attribute
                // e.q. ['array', '*.key' => 'required']
                return [is_int($wildcard) ? 'attribute' : 'wildcard' => [$wildcard => $rules]];
            })->all();

            $merged = [];

            if (array_key_exists('attribute', $groups)) {
                $merged = array_merge($merged, [$this->requestAttribute() => $groups['attribute']?->flatten()->all()]);
            }

            if (array_key_exists('wildcard', $groups)) {
                $merged = array_merge($merged, $groups['wildcard']->mapWithKeys(function ($rules) {
                    $wildcard = array_key_first($rules);

                    return [$this->requestAttribute().'.'.$wildcard => Arr::wrap($rules[$wildcard])];
                })->all());
            }

            return $merged;
        }

        return [
            $this->requestAttribute() => $rules,
        ];
    }

    /**
     * Set field custom validation error messages.
     */
    public function validationMessages(array $messages): static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get the field validation messages.
     */
    public function prepareValidationMessages(): array
    {
        return collect($this->validationMessages)->mapWithKeys(function ($message, $rule) {
            return [$this->requestAttribute().'.'.$rule => $message];
        })->all();
    }
}
