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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Facades\Fields;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldOption;
use Modules\Core\Models\DataView;
use Modules\Core\Resource\Resource;

class CustomFieldService
{
    /**
     * Get custom field by given ID.
     */
    public function find(int $id): CustomField
    {
        return CustomField::find($id);
    }

    /**
     * Get the given resource custom fields.
     */
    public static function forResource(string|Resource $resourceName): CustomFieldCollection
    {
        if ($resourceName instanceof Resource) {
            $resourceName = $resourceName->name();
        }

        return CustomFieldFileCache::get()->where('resource_name', $resourceName);
    }

    /**
     * Create new custom field in storage.
     */
    public function create(array $attributes): CustomField
    {
        $field = CustomField::create([
            'resource_name' => $attributes['resource_name'],
            'label' => $attributes['label'],
            'field_type' => $attributes['field_type'],
            'field_id' => $attributes['field_id'],
            'is_unique' => array_key_exists('is_unique', $attributes) ? $attributes['is_unique'] : null,
        ]);

        $this->createColumn($field);

        if ($field->isOptionable()) {
            return $this->createOptions($attributes['options'], $field)->load('options');
        }

        return $field;
    }

    /**
     * Create options for the given field.
     */
    public function createOptions(array $options, CustomField $field): CustomField
    {
        $options = isset($options[0]) ? $options : [$options];

        $this
            ->filterOptionsForStorage($options)
            ->each(fn (array $option, int $index) => $field->options()->create([
                'name' => $option['name'],
                'display_order' => $option['display_order'] ?? $index + 1,
                'swatch_color' => $option['swatch_color'] ?? null,
            ]));

        return $field;
    }

    /**
     * Update the field in storage.
     */
    public function update(array $attributes, int|CustomField $id): CustomField
    {
        $field = $id instanceof CustomField ? $id : $this->find($id);

        $unmarkAsUnique = Arr::pull($attributes, 'is_unique') === false && $field->is_unique;

        $field->fill(array_merge($attributes, [
            'is_unique' => $unmarkAsUnique ? false : $field->is_unique,
        ]))->save();

        if ($field->isOptionable()) {
            $this->handleFieldOptionsUpdate($field, $attributes['options']);
        }

        if ($unmarkAsUnique) {
            $this->dropUniqueIndex($field);
        }

        return $field->load('options');
    }

    /**
     * Delete custom field by given ID.
     */
    public function delete(int|CustomField $id): bool
    {
        $field = $id instanceof CustomField ? $id : $this->find($id);

        $this->dropColumn($field);

        $deleted = $field->delete();

        $this->removeAnyFieldUsageFromViewRules($field);

        return $deleted;
    }

    /**
     * Handle the field options update.
     */
    protected function handleFieldOptionsUpdate(CustomField $field, array $options): void
    {
        $optionsBeforeUpdate = $field->options;

        $this
            ->filterOptionsForStorage($options)
            ->each(function (array $option, int $index) use ($field, $optionsBeforeUpdate) {
                $attributes = [
                    'name' => $option['name'],
                    'display_order' => $option['display_order'] ?? $index + 1,
                    'swatch_color' => $option['swatch_color'] ?? null,
                ];

                if (isset($option['id'])) {
                    $optionsBeforeUpdate->find($option['id'])->fill($attributes)->save();
                } else {
                    $field->options()->create($attributes);
                }
            });

        $optionsBeforeUpdate->filter(function (CustomFieldOption $option) use ($options) {
            return ! in_array($option->id, Arr::pluck($options, 'id'));
        })->each(function (CustomFieldOption $option) use ($field) {
            // Update constraint if not multi optionable
            if (! $field->isMultiOptionable()) {
                $field->resource()
                    ->newQueryWithTrashed()
                    ->where($field->field_id, $option->id)
                    ->update([$field->field_id => null]);
            }

            $option->delete();
        });
    }

    /**
     * Sync the custom field options for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function syncOptionsForModel($model, Customfieldable&Field $field, array $ids, string $action): mixed
    {
        return $model->syncCustomFieldOptions($field, $ids);
    }

    /**
     * Create the custom field in database.
     */
    protected function createColumn(CustomField $field): void
    {
        $fromField = Fields::customFieldable()[$field->field_type];

        Schema::whenTableDoesntHaveColumn(
            $field->resource()->newModel()->getTable(),
            $field->field_id,
            function (Blueprint $table) use ($fromField, $field) {
                $fromField['className']::createValueColumn($table, $field->field_id);

                if ($field->is_unique === true && $fromField['uniqueable']) {
                    $table->unique($field->field_id, $field->uniqueIndexName());
                }
            });
    }

    /**
     * Drop the column related to the given custom field.
     */
    protected function dropColumn(CustomField $field): void
    {
        $modelTable = $field->resource()->newModel()->getTable();

        Schema::whenTableHasColumn(
            $modelTable,
            $field->field_id,
            function (Blueprint $table) use ($field, $modelTable) {
                if ($field->isOptionable()) {
                    $foreignKeys = Schema::getForeignKeysForColumn($modelTable, $field->field_id);

                    foreach ($foreignKeys as $index) {
                        $table->dropForeign($index['name']);
                    }
                }

                $table->dropColumn($field->field_id);
            });
    }

    /**
     * Drop the unique index for the given field.
     */
    protected function dropUniqueIndex(CustomField $field): void
    {
        Schema::table($field->resource()->newModel()->getTable(), function (Blueprint $table) use ($field) {
            $table->dropUnique($field->uniqueIndexName());
        });
    }

    /**
     * Remove any field usage from view rules.
     */
    protected function removeAnyFieldUsageFromViewRules(CustomField $field): void
    {
        // When model with custom fields is deleted, we will get the filters
        // which most likely are using custom field and remove them from the query object
        DataView::where('rules', 'like', '%'.$field->field_id.'%')
            ->get()
            ->each(function (DataView $view) use ($field) {
                $rules = $view->rules;

                foreach ($rules as $key => $group) {
                    $rules[$key]['children'] = $this->handleDeletedFieldViewRules($group['children'], $field);
                }

                $view->fill(['rules' => (array) $rules ?? []])->save();
            });
    }

    /**
     * Handle the deleted field rules.
     */
    protected function handleDeletedFieldViewRules(array &$rules, CustomField $field): array
    {
        $fieldId = $field->field_id;

        foreach ($rules as $key => $rule) {
            if (count($rule['query']['children'] ?? []) > 0) {
                $rule['query']['children'] = $this->handleDeletedFieldViewRules($rule['query']['children'], $field);
            } elseif ($rule['query']['rule'] == $fieldId || $rule['query']['operand'] ?? $fieldId == null) {
                unset($rules[$key]);
            }

            // If the current rule in the loop is group, we will check if the
            // group is empty and if empty, we will remove the group from the rules object.
            if ($rule['type'] === 'group' && empty($rule['query']['children'])) {
                unset($rules[$key]);
            }
        }

        return array_values($rules);
    }

    /**
     * Filter the given options for storage.
     *
     * The option name must not be empty as well all options must be unique.
     */
    protected function filterOptionsForStorage(array $options): Collection
    {
        return collect($options)->reject(
            fn ($option) => empty($option['name'])
        )->unique('name');
    }
}
